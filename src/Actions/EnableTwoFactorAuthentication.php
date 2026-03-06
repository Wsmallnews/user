<?php

namespace Wsmallnews\User\Actions;

use App\Models\User;
use Illuminate\Support\Collection;
use Wsmallnews\User\Contracts\TwoFactorAuthenticationProvider;
use Wsmallnews\User\Events\TwoFactorAuthenticationEnabled;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\RecoveryCode;

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
     * @return void
     */
    public function __construct(TwoFactorAuthenticationProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Enable two factor authentication for the user.
     *
     * @param  string  $module
     * @param  mixed  $user
     * @param  bool  $force
     * @return void
     */
    public function __invoke(string $module, User $user, bool $force = false): void
    {
        if (empty($user->two_factor_secret) || $force === true) {
            $secretLength = 16;

            $user->forceFill([
                'two_factor_secret' => UserConfig::currentEncrypter($module)->encrypt($this->provider->generateSecretKey($secretLength)),
                'two_factor_recovery_codes' => UserConfig::currentEncrypter($module)->encrypt(json_encode(Collection::times(8, function () {
                    return RecoveryCode::generate();
                })->all())),
            ])->save();

            TwoFactorAuthenticationEnabled::dispatch($user);
        }
    }
}
