<?php

namespace Wsmallnews\User\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified as BaseEnsureEmailIsVerified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Wsmallnews\User\Support\Utils;

class EnsureEmailIsVerified extends BaseEnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  string|null  $redirectToRoute
     * @return Response|RedirectResponse|null
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if (! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
            ! $request->user()->hasVerifiedEmail())) {

            session()->put('verify-previous-url', $request->fullUrl());

            return $request->expectsJson()
                ? abort(403, 'Your email address is not verified.')
                : redirect(Utils::route('verify.email'));
        }

        return $next($request);
    }
}
