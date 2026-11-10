<?php

namespace Wsmallnews\User;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Filesystem\Filesystem;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wsmallnews\Support\Features\Modules\Module;
use Wsmallnews\Support\Features\Modules\ModuleRegistry;
use Wsmallnews\User\Commands\UserInstallCommand;
use Wsmallnews\User\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Wsmallnews\User\Http\Middleware\EnsureUserIsActive;

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
            ->hasViews(static::$viewNamespace);

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
            $package->runsMigrations();
        }
    }

    public function packageRegistered(): void
    {
        ModuleRegistry::register(new Module(
            id: static::$name,
            namespace: 'Wsmallnews\User',
            plugin: UserPlugin::class,
        ));

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
        // 定义中间件别名
        $this->app['router']->aliasMiddleware('user-active', EnsureUserIsActive::class);

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

        // 注册 livewire 命名空间（自动发现 src/Livewire/ 下的组件）
        Livewire::addNamespace(
            namespace: 'sn-user',
            classNamespace: 'Wsmallnews\\User\\Livewire'
        );

        // auth 配置由使用方（cms 等）按站点注册（RegisterUserConfig），本包不再提供模块级默认。
        // 侧边栏按站点注册（见 cms 的 RegisterSiteSidebar）；user 包没有自己的站点，
        // 这里不再提供模块级默认菜单。
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
            UserInstallCommand::class,
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
        ];
    }
}
