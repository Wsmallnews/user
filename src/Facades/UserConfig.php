<?php

namespace Wsmallnews\User\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @method static static config($module, array | Closure $auth): self
 * @method static mixed getConfig($module, $name = null, $default = null, $params = [], $fieldParams = []): mixed
 * @method static bool confirmsTwoFactorAuthentication($module): bool
 * @method static \Illuminate\Contracts\Encryption\Encrypter currentEncrypter(string $module): \Illuminate\Contracts\Encryption\Encrypter
 *
 * @see \Wsmallnews\User\UserConfig
 */
class UserConfig extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wsmallnews\User\UserConfig::class;
    }
}
