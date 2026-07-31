<?php

namespace Wsmallnews\User\Http\Middleware;

use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Wsmallnews\User\Enums\Status;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        $user = $request->user($guard);

        if ($user && isset($user->status) && $user->status !== Status::Normal) {
            Auth::guard($guard)->logout();

            Notification::make()
                ->title(__('sn-user::user.notification.account_disabled'))
                ->danger()->send();
        }

        return $next($request);
    }
}
