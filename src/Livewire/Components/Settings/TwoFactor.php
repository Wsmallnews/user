<?php

namespace Wsmallnews\User\Livewire\Components\Settings;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;
use Wsmallnews\User\Facades\AuthsConfig;
use Wsmallnews\User\Livewire\Actions\ConfirmTwoFactorAuthentication;
use Wsmallnews\User\Livewire\Actions\DisableTwoFactorAuthentication;
use Wsmallnews\User\Livewire\Actions\EnableTwoFactorAuthentication;

class TwoFactor extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $formData = [];

    public string $module;

    #[Locked]
    public bool $twoFactorEnabled;

    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showModal = false;

    public bool $showVerificationStep = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    protected ?string $guard = null;

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        abort_unless(AuthsConfig::getConfig($this->module, 'two-factor.enabled', true), Response::HTTP_FORBIDDEN);

        $this->guard = AuthsConfig::getConfig($this->module, 'guard');
        if (AuthsConfig::confirmsTwoFactorAuthentication($this->module) && is_null(Auth::guard($this->guard)->user()->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication($this->module, Auth::guard($this->guard)->user());
        }

        $this->twoFactorEnabled = Auth::guard($this->guard)->user()->hasEnabledTwoFactorAuthentication($this->module);
        $this->requiresConfirmation = AuthsConfig::confirmsTwoFactorAuthentication($this->module);
    }


    public function enableAction(): Action
    {
        return Action::make('enable')
            ->label(__('Enable 2FA'))
            ->icon(Heroicon::ShieldCheck)
            // ->fillForm(fn(): array => [
            //     'setup_key' => $this->manualSetupKey,
            // ])
            ->schema(function (EnableTwoFactorAuthentication $enableTwoFactorAuthentication) {
                $user = Auth::guard($this->guard)->user();

                $enableTwoFactorAuthentication($user);

                if (! $this->requiresConfirmation) {
                    $this->twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication($this->module);
                }

                $this->loadSetupData();

                return [
                    Text::make(new HtmlString('<div class="w-full mx-auto">'.$this->qrCodeSvg.'</div>')),
                    Text::make('or, enter the code manually'),
                    Components\TextInput::make('setup_key')
                        ->hiddenLabel()
                        ->readOnly()
                        ->default($this->manualSetupKey)
                        ->copyable(copyMessage: 'Copied!', copyMessageDuration: 1500)
                ];
            })
            ->modalIcon(Heroicon::QrCode)
            ->modalHeading('Enable Two-Factor Authentication')
            ->modalDescription('To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.')
            ->modalAlignment(Alignment::Center)
            ->modalWidth(Width::Medium)
            ->rateLimit(5)
            ->action(fn() => $this->showModal = true);
    }



    /**
     * Enable two-factor authentication for the user.
     */
    public function enable(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        $user = Auth::guard($this->guard)->user();

        $enableTwoFactorAuthentication($user);

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication($this->module);
        }

        $this->loadSetupData();

        $this->showModal = true;
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = Auth::guard($this->guard)->user();

        try {
            $this->qrCodeSvg = $user?->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (\Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $user = Auth::guard($this->guard)->user();

        $this->validate();

        $confirmTwoFactorAuthentication($user, $this->code);

        $this->closeModal();

        $this->twoFactorEnabled = true;
    }

    /**
     * Reset two-factor verification state.
     */
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');

        $this->resetErrorBag();
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $user = Auth::guard($this->guard)->user();

        $disableTwoFactorAuthentication($user);

        $this->twoFactorEnabled = false;
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->reset(
            'code',
            'manualSetupKey',
            'qrCodeSvg',
            'showModal',
            'showVerificationStep',
        );

        $this->resetErrorBag();

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = Auth::guard($this->guard)->user()->hasEnabledTwoFactorAuthentication($this->module);
        }
    }

    /**
     * Get the current modal configuration state.
     */
    public function getModalConfigProperty(): array
    {
        if ($this->twoFactorEnabled) {
            return [
                'title' => __('Two-Factor Authentication Enabled'),
                'description' => __('Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.'),
                'buttonText' => __('Close'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Verify Authentication Code'),
                'description' => __('Enter the 6-digit code from your authenticator app.'),
                'buttonText' => __('Continue'),
            ];
        }

        return [
            'title' => __('Enable Two-Factor Authentication'),
            'description' => __('To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.'),
            'buttonText' => __('Continue'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\TextInput::make('password')
                    ->label('密码')
                    ->aboveLabel('敏感操作，请在继续之前确认您的密码。')
                    ->placeholder('请确认密码')
                    ->required()
                    ->password()
                    ->revealable(),
            ])
            ->statePath('formData');
    }

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $formData = $this->form->getState();

        // 当前 guard
        $guard = AuthsConfig::getConfig($this->module, 'guard');

        if (! Auth::guard($guard)->validate([
            'email' => Auth::guard($guard)->user()->email,
            'password' => $formData['password'],
        ])) {
            $this->addError('formData.password', __('auth.password'));

            return;
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: AuthsConfig::getConfig($this->module, 'urls.index'), navigate: FilamentView::hasSpaMode());
    }

    public function render()
    {
        return view('sn-user::livewire.settings.two-factor', []);
    }
}
