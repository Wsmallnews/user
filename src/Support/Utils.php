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
}
