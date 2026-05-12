<?php

namespace Wsmallnews\User;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wsmallnews\User\Commands\UserCommand;
use Wsmallnews\User\Components\Address;
use Wsmallnews\User\Components\ChooseAddress;
use Wsmallnews\User\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Wsmallnews\User\Facades\SidebarMenuRegistry as SidebarMenuRegistryFacade;
use Wsmallnews\User\Facades\UserConfig as UserConfigFacade;
use Wsmallnews\User\Http\Middleware\Authenticate;
use Wsmallnews\User\Http\Middleware\EnsureEmailIsVerified;
use Wsmallnews\User\Http\Middleware\RedirectIfAuthenticated;
use Wsmallnews\User\Http\Middleware\RequirePassword;
use Wsmallnews\User\Livewire\Components\Auth\ConfirmPassword;
use Wsmallnews\User\Livewire\Components\Auth\ForgotPassword;
use Wsmallnews\User\Livewire\Components\Auth\Login;
use Wsmallnews\User\Livewire\Components\Auth\Register;
use Wsmallnews\User\Livewire\Components\Auth\ResetPassword;
use Wsmallnews\User\Livewire\Components\Auth\VerifyEmail;
use Wsmallnews\User\Livewire\Components\Settings\Password;
use Wsmallnews\User\Livewire\Components\Settings\Profile;
use Wsmallnews\User\Livewire\Components\Settings\TwoFactor;
use Wsmallnews\User\Livewire\Components\Settings\TwoFactor\RecoveryCodes;
use Wsmallnews\User\Livewire\Components\User\Menu as UserMenu;
use Wsmallnews\User\Livewire\Components\User\Profile as UserProfile;
use Wsmallnews\User\Livewire\Components\User\SidebarMenu;
use Wsmallnews\User\Support\Utils;

class UserServiceProvider extends PackageServiceProvider
{
    public static string $name = 'sn-user';

    public static string $viewNamespace = 'sn-user';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews(static::$viewNamespace)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('wsmallnews/user');
            });

        if (Utils::getConfig('routes.enabled') !== false) {     // 只要不等于 false 就注册路由
            $package->hasRoutes($this->getRoutes());
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
            $package->runsMigrations();
        }
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(TwoFactorAuthenticationProviderContract::class, function ($app): TwoFactorAuthenticationProviderContract {
            return new TwoFactorAuthenticationProvider(
                $app->make(Google2FA::class),
                $app->make(Repository::class)
            );
        });

        // 注册用户侧边栏菜单
        $this->app->singleton(SidebarMenuRegistry::class, function () {
            return new SidebarMenuRegistry;
        });
    }

    public function packageBooted(): void
    {
        // 注册模型别名
        Relation::enforceMorphMap([
            'sn_user_address' => 'Wsmallnews\User\Models\Address',
        ]);

        // 定义中间件别名
        $this->app['router']->aliasMiddleware('user-auth', Authenticate::class);
        $this->app['router']->aliasMiddleware('user-guest', RedirectIfAuthenticated::class);
        $this->app['router']->aliasMiddleware('user-password.confirm', RequirePassword::class);
        $this->app['router']->aliasMiddleware('user-email.verified', EnsureEmailIsVerified::class);

        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/user/{$file->getFilename()}"),
                ], 'user-stubs');
            }
        }

        Livewire::component('sn-user-components-auth-login', Login::class);
        Livewire::component('sn-user-components-auth-register', Register::class);
        Livewire::component('sn-user-components-auth-forgot-password', ForgotPassword::class);
        Livewire::component('sn-user-components-auth-reset-password', ResetPassword::class);
        Livewire::component('sn-user-components-auth-verify-email', VerifyEmail::class);
        Livewire::component('sn-user-components-auth-confirm-password', ConfirmPassword::class);

        Livewire::component('sn-user-components-user-menu', UserMenu::class);
        Livewire::component('sn-user-components-user-sidebar-menu', SidebarMenu::class);
        Livewire::component('sn-user-components-user-profile', UserProfile::class);

        // 用户设置
        Livewire::component('sn-user-components-settings-two-factor', TwoFactor::class);
        Livewire::component('sn-user-components-settings-two-factor-recovery-codes', RecoveryCodes::class);
        Livewire::component('sn-user-components-settings-profile', Profile::class);
        Livewire::component('sn-user-components-settings-password', Password::class);

        // // 管理收货地址
        // Livewire::component('sn-user-address', Address::class);
        // // 选择收货地址
        // Livewire::component('sn-user-choose-address', ChooseAddress::class);

        // 注册用户认证信息
        UserConfigFacade::config(app(UserPlugin::class)->getId(), function () {
            return [
                'guard' => Utils::getConfig('guard', 'web'),
                'two_factor' => Utils::getConfig('two_factor', []),
                'urls' => [
                    'index' => Utils::route('index'),           // @sn todo 用户默认页面的首页不应该是这个路由
                    'login' => Utils::route('login'),
                    'register' => Utils::route('register'),
                    'profile' => Utils::route('profile'),
                    'forgot-password' => Utils::route('forgot.password'),
                    'reset-password' => fn ($params) => Utils::route('reset.password', $params),
                    'verify-email' => Utils::route('verify.email'),
                    'verify-email-verification' => function ($parameters) {
                        // @sn todo ，这里先直接填入 租户参数
                        if (! isset($parameters['tenant'])) {        // 没有租户参数,则添加租户参数
                            $tenant = current_tenant();
                            $parameters['tenant'] = $tenant;        // 租户参数
                        }

                        $parameters['module'] = app(UserPlugin::class)->getId();             // 当前模块名

                        return URL::temporarySignedRoute(
                            Utils::getConfig('routes.name', '') . 'verify.email.verification',
                            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                            $parameters
                        );
                    },
                    'password-confirm' => Utils::route('password.confirm'),
                ],
            ];
        });

        // 注册用户侧边栏菜单
        SidebarMenuRegistryFacade::registers(app(UserPlugin::class)->getId(), [
            fn () => [
                'key' => 'profile',
                'label' => __('sn-user::user.settings.sidebar.profile'),
                'url' => Utils::route('profile'),
                'icon' => Heroicon::OutlinedUser,
                'active_icon' => Heroicon::User,
            ],
            fn () => [
                'key' => 'settings-profile',
                'label' => __('sn-user::user.settings.sidebar.settings_profile'),
                'url' => Utils::route('settings.profile'),
                'icon' => Heroicon::OutlinedPencilSquare,
                'active_icon' => Heroicon::PencilSquare,
            ],
            fn () => [
                'key' => 'settings-password',
                'label' => __('sn-user::user.settings.sidebar.settings_password'),
                'url' => Utils::route('settings.password'),
                'icon' => Heroicon::OutlinedLockClosed,
                'active_icon' => Heroicon::LockClosed,
            ],
            fn () => [
                'key' => 'settings-two-factor',
                'label' => __('sn-user::user.settings.sidebar.settings_two_factor'),
                'url' => fn () => Utils::route('settings.two-factor'),
                'icon' => Heroicon::OutlinedKey,
                'active_icon' => Heroicon::Key,
                'hidden' => fn () => ! Utils::getConfig('two_factor.enabled', false),
            ],
        ]);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'wsmallnews/user';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('user', __DIR__ . '/../resources/dist/components/user.js'),
            // Css::make('user-styles', __DIR__ . '/../resources/dist/user.css'),
            // Js::make('user-scripts', __DIR__ . '/../resources/dist/user.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            UserCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return ['web'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            '2025_01_20_113724_add_two_factor_columns_to_users_table',
            '2025_01_20_113724_add_user_columns_to_users_table',
            // '2025_02_28_111049_create_sn_user_addresses_table',
        ];
    }
}
