<?php

namespace Wsmallnews\User\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Wsmallnews\User\User
 */
class User extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wsmallnews\User\User::class;
    }
}
