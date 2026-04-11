<?php

namespace Wsmallnews\User;

use Closure;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class UserConfig
{
    // $auth = [
    //     'guard' => 'web',
    //     'two_factor' => [
    //         'enabled' => true,
    //         'confirm' => true,
    //         'window' => 1,
    //     ],
    //     @var \Illuminate\Contracts\Encryption\Encrypter|null
    //     encrypter => null,
    //     'urls' => [
    //         'index' => '/index',
    //         'login' => '/login',
    //         'register' => '/register',
    //         'profile' => '/profile',
    //         'forgot-password' => '/forgot-password',
    //         'reset-password' => '/reset-password/{token}',
    //         'verify-email' => '/verify-email',
    //         'verify-email-verification' => '/verify-email/{id}/{hash}',
    //         'password-confirm' => '/password-confirm',
    //         'two-factor' => '/two-factor',
    //     ],
    // ];
    protected ?Collection $auths;

    public function __construct()
    {
        $this->auths = collect();
    }

    public function config($module, array | Closure $auth): self
    {
        $this->auths->put($module, $auth);

        return $this;
    }

    /**
     * 获取设置
     *
     * @param  string  $module  所属模块
     * @param  string  $name  设置项名称
     * @param  mixed  $default  默认值
     * @param  array  $params  配置动态参数
     * @param  array  $fieldParams  字段参数
     */
    public function getConfig($module, $name = null, $default = null, $params = [], $fieldParams = []): mixed
    {
        $module = $this->auths->get($module);

        $config = $module instanceof Closure ? $module($params) : $module;

        if ($name) {
            $nameValue = data_get($config, $name) ?? $default;
            if ($nameValue instanceof Closure) {
                $nameValue = $nameValue($fieldParams);
            }

            return $nameValue;
        }

        return $config;
    }

    /**
     * 确认是否开启了双因素认证
     *
     * @param  string  $module  所属模块
     */
    public function confirmsTwoFactorAuthentication($module): bool
    {
        return $this->getConfig($module, 'two_factor.enabled', false) && $this->getConfig($module, 'two_factor.confirm', false);
    }

    /**
     * Get the current encrypter being used by the model.
     */
    public function currentEncrypter(string $module): Encrypter
    {
        return $this->getConfig($module, 'encrypter', null) ?? Model::$encrypter ?? Crypt::getFacadeRoot();
    }
}
