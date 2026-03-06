<?php

namespace Wsmallnews\User\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Wsmallnews\User\Contracts\TwoFactorAuthenticationProvider;
use Wsmallnews\User\Events\TwoFactorAuthenticationConfirmed;
use Wsmallnews\User\Facades\UserConfig;

class ConfirmTwoFactorAuthentication
{
    /**
     * The two factor authentication provider.
     *
     * @var TwoFactorAuthenticationProvider
     */
    protected $provider;

    /**
     * Create a new action instance.
     *
     * @return void
     */
    public function __construct(TwoFactorAuthenticationProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Confirm the two factor authentication configuration for the user.
     *
     * @param  string  $module
     * @param  mixed  $user
     * @param  string  $code
     * @return void
     */
    public function __invoke(string $module, User $user, $code, $statePath = null)
    {
        if (empty($user->two_factor_secret) ||
            empty($code) ||
            ! $this->provider->verify($module, UserConfig::currentEncrypter($module)->decrypt($user->two_factor_secret), $code)) {
            throw ValidationException::withMessages([
                ($statePath ? $statePath . '.' : '') . 'code' => [__('The provided two factor authentication code was invalid.')],
            ])->errorBag('confirmTwoFactorAuthentication');
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        TwoFactorAuthenticationConfirmed::dispatch($user);
    }
}
