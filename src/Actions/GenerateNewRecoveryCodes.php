<?php

namespace Wsmallnews\User\Actions;

use App\Models\User;
use Illuminate\Support\Collection;
use Wsmallnews\Support\Features\Site\Site;
use Wsmallnews\User\Events\RecoveryCodesGenerated;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\RecoveryCode;

class GenerateNewRecoveryCodes
{
    /**
     * Generate new recovery codes for the user.
     *
     * @param  mixed  $user
     */
    public function __invoke(string $module, Site $site, User $user): void
    {
        $user->forceFill([
            'two_factor_recovery_codes' => UserConfig::currentEncrypter()->encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ])->save();

        RecoveryCodesGenerated::dispatch($user);
    }
}
