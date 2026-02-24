<?php

namespace Wsmallnews\User\Http\Middleware;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated as BaseRedirectIfAuthenticated;
use Illuminate\Http\Request;
use Wsmallnews\User\Support\Utils;

class RedirectIfAuthenticated extends BaseRedirectIfAuthenticated
{
    /**
     * Get the path the user should be redirected to when they are authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return Utils::route('profile');
    }
}
