<?php

namespace Wsmallnews\User\Livewire\Components\Settings\TwoFactor;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Wsmallnews\User\Facades\AuthsConfig;
use Wsmallnews\User\Livewire\Actions\GenerateNewRecoveryCodes;

class RecoveryCodes extends Component
{
    #[Locked]
    public array $recoveryCodes = [];

    public string $module;

    protected ?string $guard = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->guard = AuthsConfig::getConfig($this->module, 'guard');

        $this->loadRecoveryCodes();
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(Auth::guard($this->guard)->user());

        $this->loadRecoveryCodes();
    }

    /**
     * Load the recovery codes for the user.
     */
    private function loadRecoveryCodes(): void
    {
        $user = Auth::guard($this->guard)->user();

        if ($user->hasEnabledTwoFactorAuthentication($this->module) && $user->two_factor_recovery_codes) {
            try {
                $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            } catch (\Exception) {
                $this->addError('recoveryCodes', 'Failed to load recovery codes');

                $this->recoveryCodes = [];
            }
        }
    }

    public function render()
    {
        return view('sn-user::livewire.settings.two-factor.recovery-codes', []);
    }
}
