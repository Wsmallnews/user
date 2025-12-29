<?php

declare(strict_types=1);

namespace Wsmallnews\User\Support;

class Utils
{

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
