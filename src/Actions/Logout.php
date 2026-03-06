<?php

namespace Wsmallnews\User\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Wsmallnews\User\Facades\UserConfig;

class Logout
{
    /**
     * Log the current user out of the application.
     *
     * @param  string  $guard
     */
    public function __invoke(string $module): void
    {
        $guard = UserConfig::getConfig($module, 'guard');

        // 退出登录
        Auth::guard($guard)->logout();

        Session::invalidate();
        Session::regenerateToken();
    }
}
