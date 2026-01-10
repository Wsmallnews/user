<?php

namespace Wsmallnews\User\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Wsmallnews\User\Facades\UserConfig;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $module = $request->input('module', 'default');

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(UserConfig::getConfig($module, 'urls.profile') . '?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(UserConfig::getConfig($module, 'urls.profile') . '?verified=1');
    }
}
