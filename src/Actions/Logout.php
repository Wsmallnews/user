<?php

namespace Wsmallnews\User\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(string $guard = 'web'): void
    {
        Auth::guard($guard)->logout();

        Session::invalidate();
        Session::regenerateToken();
    }
}
