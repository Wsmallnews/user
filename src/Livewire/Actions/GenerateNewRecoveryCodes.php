<?php

namespace Wsmallnews\User\Livewire\Actions;

use Illuminate\Support\Collection;
use Wsmallnews\User\Events\RecoveryCodesGenerated;
use Wsmallnews\User\Facades\AuthsConfig;
use Wsmallnews\User\RecoveryCode;

class GenerateNewRecoveryCodes
{
    /**
     * Generate new recovery codes for the user.
     *
     * @param  mixed  $user
     * @return void
     */
    public function __invoke($user)
    {
        $user->forceFill([
            'two_factor_recovery_codes' => AuthsConfig::currentEncrypter()->encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ])->save();

        RecoveryCodesGenerated::dispatch($user);
    }
}
