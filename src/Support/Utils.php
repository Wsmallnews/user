<?php

declare(strict_types=1);

namespace Wsmallnews\User\Support;

use App\Models\User;
use Wsmallnews\User\Exceptions\UserException;

class Utils
{
    public static function getConfig($name = null, $default = null)
    {
        $config = config('sn-user');

        return $name ? (data_get($config, $name) ?? $default) : $config;
    }

    /**
     * Get panel register raw config.
     *
     * @param  string  $type  Register type (pages or resources)
     */
    public static function getPanelRegister(?string $type = 'pages'): mixed
    {
        if (blank($type)) {
            return self::getConfig('panel_register', null);
        }

        return self::getConfig("panel_register.$type", null);
    }

    /**
     * 获取模型
     */
    public static function getModel(string $name, bool $shouldException = true): ?string
    {
        $model = self::getConfig('models')[$name] ?? null;

        if (blank($model) && $shouldException) {
            throw new UserException("Model {$name} not found.");
        }

        return $model;
    }

    /**
     * 获取用户模型
     *
     * @return User
     */
    public static function getUserModel(): string
    {
        return self::getModel('user');
    }

    /**
     * 获取文件目录
     *
     * @param  string|null  $type  目录类型
     * @return string
     */
    public static function getFileDirectory($type = null)
    {
        return self::getConfig('file_directory', 'sn/user/') . ($type ? $type . '/' : '') . date('Ymd');
    }

    /**
     * 获取主题模式
     */
    public static function getDefaultDarkMode(): string
    {
        return self::getConfig('themes.default_dark_mode', 'system');
    }

    /**
     * 是否启用暗黑模式
     */
    public static function hasDarkMode(): bool
    {
        return self::getConfig('themes.dark_mode', false);
    }

    /**
     * 是否强制暗黑主题
     */
    public static function hasDarkModeForced(): bool
    {
        return self::getConfig('themes.dark_mode_forced', false);
    }

    /**
     * 获取当前布局
     */
    public static function getLayout(): string
    {
        return self::getConfig('themes.layout', 'sn-user::components.layouts.app');
    }

    /**
     * 获取当前页面容器
     */
    public static function getPageContainer(): string
    {
        return self::getConfig('themes.page_container', 'sn-user::container.page');
    }

    /**
     * 获取当前页面命名空间
     */
    public static function getViewNamespace(): string
    {
        return self::getConfig('themes.view_namespace', 'sn-user::livewire.');
    }

    /**
     * cms 内部路由处理
     *
     * @param  string  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     */
    public static function route($name, $parameters = [], $absolute = true): string
    {
        $name = self::getConfig('routes.name', '') . $name;

        return sn_route($name, $parameters, $absolute);
    }
}
