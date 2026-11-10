<?php

namespace Wsmallnews\User\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @method static static config($module, array | Closure $auth, \Wsmallnews\Support\Features\Site\Site $site): self
 * @method static mixed getConfig($module, \Wsmallnews\Support\Features\Site\Site $site, $name = null, $default = null, $params = [], $fieldParams = []): mixed
 * @method static bool confirmsTwoFactorAuthentication($module, \Wsmallnews\Support\Features\Site\Site $site): bool
 * @method static \Illuminate\Contracts\Encryption\Encrypter currentEncrypter(string $module, \Wsmallnews\Support\Features\Site\Site $site): \Illuminate\Contracts\Encryption\Encrypter
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
