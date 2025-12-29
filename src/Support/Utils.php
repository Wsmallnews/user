<?php

declare(strict_types=1);

namespace Wsmallnews\User\Support;

class Utils
{
    public static $route = [
        // 'sn-module' => [
        //     'login' => 'login',
        //     'register' => 'register',
        //     // ...
        // ]
    ];

    /**
     * 设置路由
     *
     * @param [type] $module
     * @param [type] $routes
     * @return void
     */
    public static function setRoutes($module, $routes)
    {
        if (isset(self::$route[$module])) {
            self::$route[$module] = array_merge(self::$route[$module], $routes);
        } else {
            self::$route[$module] = $routes;
        }
    }

    public static function setRoute($module, $key, $route)
    {
        self::setRoutes($module, [$key => $route]);
    }

    public static function getRoute($module, $key, $default = null)
    {
        return self::$route[$module][$key] ?? $default;
    }

    public static function getConfig($name = null, $default = null)
    {
        $config = config('sn-user');

        return $name ? (data_get($config, $name) ?? $default) : $config;
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
}
