<?php

namespace Wsmallnews\User\Actions;

use Wsmallnews\User\Events\TwoFactorAuthenticationDisabled;
use Wsmallnews\User\Facades\UserConfig;

class DisableTwoFactorAuthentication
{
    /**
     * Disable two factor authentication for the user.
     *
     * @param  mixed  $user
     * @return void
     */
    public function __invoke($module, $user)
    {
        if (! is_null($user->two_factor_secret) ||
            ! is_null($user->two_factor_recovery_codes) ||
            ! is_null($user->two_factor_confirmed_at)) {
            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
            ] + (UserConfig::confirmsTwoFactorAuthentication($module) || ! is_null($user->two_factor_confirmed_at) ? [
                'two_factor_confirmed_at' => null,
            ] : []))->save();

            TwoFactorAuthenticationDisabled::dispatch($user);
        }
    }
}
