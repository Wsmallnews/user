<?php

namespace Wsmallnews\User\Livewire\Components\Settings\TwoFactor;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Wsmallnews\User\Events\RecoveryCodesGenerated;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\RecoveryCode;

class RecoveryCodes extends Component implements HasActions, HasSchemas
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
            ->label(__('Regenerate Recovery Codes'))
            ->icon(Heroicon::ArrowPath)
            ->color('gray')
            ->requiresConfirmation()
            ->modalIconColor('danger')
            ->modalHeading('Regenerate Recovery Codes')
            ->modalDescription('Are you sure you\'d like to regenerate recovery codes? ')
            ->successNotificationTitle(__('Recovery codes regenerated successfully'))
            ->action(function () {
                $user = Auth::guard($this->guard)->user();

                $this->generateNewRecoveryCodes($user);

                $this->loadRecoveryCodes();
            });
    }

    protected function generateNewRecoveryCodes($user): void
    {
        $user->forceFill([
            'two_factor_recovery_codes' => UserConfig::currentEncrypter($this->module)->encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ])->save();

        RecoveryCodesGenerated::dispatch($user);
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
        return view('sn-user::livewire.components.settings.two-factor.recovery-codes', []);
    }
}
