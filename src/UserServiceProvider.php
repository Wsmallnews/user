<?php

namespace Wsmallnews\User;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wsmallnews\User\Commands\UserCommand;
use Wsmallnews\User\Components\Address;
use Wsmallnews\User\Components\ChooseAddress;
use Wsmallnews\User\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Wsmallnews\User\Livewire\Components\Auth\ConfirmPassword;
use Wsmallnews\User\Livewire\Components\Auth\ForgotPassword;
use Wsmallnews\User\Livewire\Components\Auth\Login;
use Wsmallnews\User\Livewire\Components\Auth\Register;
use Wsmallnews\User\Livewire\Components\Auth\ResetPassword;
use Wsmallnews\User\Livewire\Components\Auth\VerifyEmail;
use Wsmallnews\User\Livewire\Components\Settings\TwoFactor;
use Wsmallnews\User\Livewire\Components\Settings\TwoFactor\RecoveryCodes;
use Wsmallnews\User\Livewire\Components\Settings\Profile;
use Wsmallnews\User\Livewire\Components\Settings\Password;
use Wsmallnews\User\Livewire\Components\User\UserMenu;

class UserServiceProvider extends PackageServiceProvider
{
    public static string $name = 'sn-user';

    public static string $viewNamespace = 'sn-user';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('wsmallnews/user');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
            $package->runsMigrations();
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(TwoFactorAuthenticationProviderContract::class, function ($app) {
            return new TwoFactorAuthenticationProvider(
                $app->make(Google2FA::class),
                $app->make(Repository::class)
            );
        });
    }

    public function packageBooted(): void
    {
        // 注册模型别名
        Relation::enforceMorphMap([
            'sn_user_address' => 'Wsmallnews\User\Models\Address',
        ]);

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

        Livewire::component('sn-user-components-user-user-menu', UserMenu::class);

        // 用户设置
        Livewire::component('sn-user-components-settings-two-factor', TwoFactor::class);
        Livewire::component('sn-user-components-settings-two-factor-recovery-codes', RecoveryCodes::class);
        Livewire::component('sn-user-components-settings-profile', Profile::class);
        Livewire::component('sn-user-components-settings-password', Password::class);

        // // 管理收货地址
        // Livewire::component('sn-user-address', Address::class);
        // // 选择收货地址
        // Livewire::component('sn-user-choose-address', ChooseAddress::class);

        // $actions = config('sn-user.actions');
        // Fortify::createUsersUsing($actions['create_new_user']);
        // Fortify::updateUserProfileInformationUsing($actions['update_user_profile_information']);
        // Fortify::updateUserPasswordsUsing($actions['update_user_password']);
        // Fortify::resetUserPasswordsUsing($actions['reset_user_password']);
        // Fortify::redirectUserForTwoFactorAuthenticationUsing($actions['redirect_if_two_factor_authenticatable']);

        // RateLimiter::for('login', function (Request $request) {
        //     $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

        //     return Limit::perMinute(5)->by($throttleKey);
        // });

        // RateLimiter::for('two-factor', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->session()->get('login.id'));
        // });

        // // 注册所有认证页面
        // Fortify::viewPrefix('sn-user::auth.');
        // Fortify 逻辑注册完毕
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
        return [];
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
