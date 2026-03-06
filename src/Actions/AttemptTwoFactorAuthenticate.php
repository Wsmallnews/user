<?php

namespace Wsmallnews\User\Actions;

use App\Models\User;
use Wsmallnews\User\Contracts\TwoFactorAuthenticationProvider;
use Wsmallnews\User\Facades\UserConfig;

class AttemptTwoFactorAuthenticate
{
    protected string $module;

    protected array $formData;

    /**
     * AttemptTwoFactorAuthenticate constructor.
     */
    public function __construct(string $module, array $formData)
    {
        $this->module = $module;
        $this->formData = $formData;
    }

    public function __invoke(User $user): bool
    {
        $code = $this->formData['code'] ?? null;
        $recoveryCode = $this->formData['recoveryCode'] ?? null;

        if ($recoveryCode) {
            $currentRecoveryCode = collect($user->recoveryCodes($this->module))->first(function ($code) use ($recoveryCode) {
                return hash_equals($code, $recoveryCode) ? $code : null;
            });

            if ($currentRecoveryCode) {
                // 恢复码验证成功， 替换为新的恢复码
                $user->replaceRecoveryCode($this->module, $currentRecoveryCode);

                return true;
            }

            // 恢复码验证失败
            return false;
        } else {
            return $code && app(TwoFactorAuthenticationProvider::class)->verify(
                $this->module,
                UserConfig::currentEncrypter($this->module)->decrypt($user->two_factor_secret),
                $code
            );
        }
    }
}
