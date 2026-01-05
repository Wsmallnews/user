<?php

namespace Wsmallnews\User\Livewire\Actions;

use Illuminate\Support\Collection;
use Wsmallnews\User\Contracts\TwoFactorAuthenticationProvider;
use Wsmallnews\User\RecoveryCode;
use Wsmallnews\User\Events\TwoFactorAuthenticationEnabled;
use Wsmallnews\User\AuthsConfig;

class EnableTwoFactorAuthentication
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
     * @param  TwoFactorAuthenticationProvider  $provider
     * @return void
     */
    public function __construct(TwoFactorAuthenticationProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Enable two factor authentication for the user.
     *
     * @param  mixed  $user
     * @param  bool  $force
     * @return void
     */
    public function __invoke($user, $force = false)
    {
        if (empty($user->two_factor_secret) || $force === true) {
            $secretLength = 16;

            $user->forceFill([
                'two_factor_secret' => AuthsConfig::currentEncrypter()->encrypt($this->provider->generateSecretKey($secretLength)),
                'two_factor_recovery_codes' => AuthsConfig::currentEncrypter()->encrypt(json_encode(Collection::times(8, function () {
                    return RecoveryCode::generate();
                })->all())),
            ])->save();

            TwoFactorAuthenticationEnabled::dispatch($user);
        }
    }
}
