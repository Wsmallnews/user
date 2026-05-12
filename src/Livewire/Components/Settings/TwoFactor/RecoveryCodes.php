<?php

namespace Wsmallnews\User\Livewire\Components\Settings\TwoFactor;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Wsmallnews\User\Actions\GenerateNewRecoveryCodes;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Livewire\Components\Base;

class RecoveryCodes extends Base implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    #[Locked]
    public array $recoveryCodes = [];

    #[Locked]
    public string $module;

    protected ?string $guard = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->guard = UserConfig::getConfig($this->module, 'guard');

        $this->loadRecoveryCodes();
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateAction(): Action
    {
        return Action::make('regenerate')
            ->label(__('sn-user::user.settings.recovery_codes.regenerate'))
            ->icon(Heroicon::ArrowPath)
            ->color('gray')
            ->requiresConfirmation()
            ->modalIconColor('danger')
            ->modalHeading(__('sn-user::user.settings.recovery_codes.regenerate_confirmation_title'))
            ->modalDescription(__('sn-user::user.settings.recovery_codes.regenerate_confirmation_description'))
            ->successNotificationTitle(__('sn-user::user.settings.recovery_codes.regenerate_success'))
            ->action(function (GenerateNewRecoveryCodes $generateNewRecoveryCodes) {
                $user = Auth::guard($this->guard)->user();

                $generateNewRecoveryCodes($this->module, $user);

                $this->loadRecoveryCodes();
            });
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
                $this->addError('recoveryCodes', __('sn-user::user.settings.recovery_codes.load_failed'));

                $this->recoveryCodes = [];
            }
        }
    }

    public function render()
    {
        return view('sn-user::livewire.components.settings.two-factor.recovery-codes', []);
    }
}
