<?php

namespace Wsmallnews\User\Livewire\Components\Settings;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;
use Wsmallnews\User\Actions\ConfirmTwoFactorAuthentication;
use Wsmallnews\User\Actions\DisableTwoFactorAuthentication;
use Wsmallnews\User\Actions\EnableTwoFactorAuthentication;
use Wsmallnews\User\Facades\UserConfig;

class TwoFactor extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $formData = [];

    #[Locked]
    public string $module;

    #[Locked]
    public bool $twoFactorEnabled;      // 当前用户是否启用双因素

    #[Locked]
    public bool $requiresConfirmation;  // 双因素启用，是否必须确认才可启用成功

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public array $flash = [];

    protected ?string $guard = null;

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        abort_unless(UserConfig::getConfig($this->module, 'two_factor.enabled', true), Response::HTTP_FORBIDDEN);

        $this->guard = UserConfig::getConfig($this->module, 'guard');
        if (UserConfig::confirmsTwoFactorAuthentication($this->module) && is_null(Auth::guard($this->guard)->user()->two_factor_confirmed_at)) {
            // 如果用户未确认，双因素启用失败，清空 two_factor_secret， two_factor_recovery_codes 数据
            $disableTwoFactorAuthentication($this->module, Auth::guard($this->guard)->user());
        }

        $this->twoFactorEnabled = Auth::guard($this->guard)->user()->hasEnabledTwoFactorAuthentication($this->module);
        $this->requiresConfirmation = UserConfig::confirmsTwoFactorAuthentication($this->module);
    }

    public function enableAction(): Action
    {
        return Action::make('enable')
            ->label(__('Enable 2FA'))
            ->icon(Heroicon::ShieldCheck)
            ->schema(function (EnableTwoFactorAuthentication $enableTwoFactorAuthentication) {
                $user = Auth::guard($this->guard)->user();

                // 填充双因素字段
                $enableTwoFactorAuthentication($this->module, $user);

                if (! $this->requiresConfirmation) {
                    // 如果不需要确认，直接启用双因素
                    $this->twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication($this->module);
                }

                $this->loadSetupData();

                return [
                    Text::make(new HtmlString('<div class="p-4 border border-gray-300 dark:border-gray-700 rounded-md">' . $this->qrCodeSvg . '</div>'))
                        ->extraAttributes([
                            'class' => 'w-full flex justify-center items-center',
                        ]),

                    Action::make('close')       // 不需要确认，关闭当前modal
                        ->label(__('Close'))
                        ->cancelParentActions()     // 确认后将父级modal 也关闭
                        ->extraAttributes([
                            'class' => 'w-full',
                        ])
                        ->action(function () {})
                        ->visible(! $this->requiresConfirmation),
                    Action::make('continue')
                        ->label(__('Continue'))
                        ->schema(function () {
                            return [
                                Components\OneTimeCodeInput::make('code')
                                    ->label(__('Code'))
                                    ->hiddenLabel()
                                    ->required()
                                    ->extraAttributes(['class' => 'mx-auto'], true),
                            ];
                        })
                        ->modalIcon(Heroicon::QrCode)
                        ->modalHeading('Verify Authentication Code')
                        ->modalDescription('Enter the 6-digit code from your authenticator app.')
                        ->modalAlignment(Alignment::Center)
                        ->modalWidth(Width::Medium)
                        ->modalCancelActionLabel(__('Back'))
                        ->modalSubmitActionLabel(__('Confirm'))
                        ->modalFooterActionsAlignment(Alignment::Center)
                        ->closeModalByClickingAway(false)
                        ->cancelParentActions()     // 确认后将父级modal 也关闭
                        ->extraAttributes([
                            'class' => 'w-full',
                        ])
                        ->successNotificationTitle(__('2FA enabled successfully'))
                        ->action(function (ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication, array $data) {
                            $user = Auth::guard($this->guard)->user();

                            $confirmTwoFactorAuthentication($this->module, $user, (string) $data['code'] ?? '', 'mountedActions.1.data');

                            $this->twoFactorEnabled = true;     // 用户双因素启用成功
                        })
                        ->visible($this->requiresConfirmation),
                    Text::make('or, enter the code manually')
                        ->view('sn-user::filament.schema.divide'),
                    Components\TextInput::make('setup_key')
                        ->hiddenLabel()
                        ->readOnly()
                        ->default($this->manualSetupKey)
                        ->copyable(copyMessage: 'Copied!', copyMessageDuration: 1500),
                ];
            })
            ->modalIcon(Heroicon::QrCode)
            ->modalHeading(function () {
                return $this->requiresConfirmation
                    ? __('Enable Two-Factor Authentication')
                    : __('Two-Factor Authentication Enabled');
            })
            ->modalDescription(function () {
                match ($this->requiresConfirmation) {
                    true => __('To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.'),
                    false => __('Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.'),
                };
            })
            ->modalAlignment(Alignment::Center)
            ->modalWidth(Width::Medium)
            ->modalAutofocus(false)
            ->closeModalByClickingAway(false)
            ->rateLimit(5)
            ->modalSubmitAction(false)
            ->modalCancelAction(false);
    }

    public function disableAction(): Action
    {
        return Action::make('disable')
            ->label(__('Disable 2FA'))
            ->icon(Heroicon::ShieldExclamation)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Disable 2FA')
            ->modalDescription('Are you sure you\'d like to disable two-factor authentication? ')
            ->action(function (DisableTwoFactorAuthentication $disableTwoFactorAuthentication) {
                $user = Auth::guard($this->guard)->user();

                $disableTwoFactorAuthentication($this->module, $user);

                $this->twoFactorEnabled = false;
            });
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = Auth::guard($this->guard)->user();

        try {
            $this->qrCodeSvg = $user?->twoFactorQrCodeSvg($this->module);
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (\Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    public function render()
    {
        return view('sn-user::livewire.components.settings.two-factor', []);
    }
}
