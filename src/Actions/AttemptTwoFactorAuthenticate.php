<?php

namespace Wsmallnews\User\Actions;

use App\Models\User;
use Wsmallnews\Support\Features\Site\Site;
use Wsmallnews\User\Contracts\TwoFactorAuthenticationProvider;
use Wsmallnews\User\Facades\UserConfig;

class AttemptTwoFactorAuthenticate
{
    protected string $module;

    protected Site $site;

    protected array $formData;

    /**
     * AttemptTwoFactorAuthenticate constructor.
     */
    public function __construct(string $module, Site $site, array $formData)
    {
        $this->module = $module;
        $this->site = $site;
        $this->formData = $formData;
    }

    public function __invoke(User $user): bool
    {
        $code = $this->formData['code'] ?? null;
        $recoveryCode = $this->formData['recoveryCode'] ?? null;

        if ($recoveryCode) {
            $currentRecoveryCode = collect($user->recoveryCodes($this->site))->first(function ($code) use ($recoveryCode) {
                return hash_equals($code, $recoveryCode) ? $code : null;
            });

            if ($currentRecoveryCode) {
                // 恢复码验证成功， 替换为新的恢复码
                $user->replaceRecoveryCode($this->site, $currentRecoveryCode);

                return true;
            }

            // 恢复码验证失败
            return false;
        } else {
            return $code && app(TwoFactorAuthenticationProvider::class)->verify(
                $this->module,
                UserConfig::currentEncrypter()->decrypt($user->two_factor_secret),
                $code
            );
        }
    }
}
