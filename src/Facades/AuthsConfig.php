<?php

namespace Wsmallnews\User\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @method static static config($module, array | Closure $auth): self
 * @method static static getConfig($module, $name = null, $default = null): mixed
 *
 * @see \Wsmallnews\User\AuthsConfig
 */
class AuthsConfig extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wsmallnews\User\AuthsConfig::class;
    }
}
