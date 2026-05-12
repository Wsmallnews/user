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
use Symfony\Component\HttpFoundation\Response;
use Wsmallnews\User\Actions\ConfirmTwoFactorAuthentication;
use Wsmallnews\User\Actions\DisableTwoFactorAuthentication;
use Wsmallnews\User\Actions\EnableTwoFactorAuthentication;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Livewire\Components\Base;

class TwoFactor extends Base implements HasActions, HasSchemas
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
            ->label(__('sn-user::user.settings.two_factor.enable'))
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
                        ->label(__('sn-user::user.settings.two_factor.close'))
                        ->cancelParentActions()     // 确认后将父级modal 也关闭
                        ->extraAttributes([
                            'class' => 'w-full',
                        ])
                        ->action(function () {})
                        ->visible(! $this->requiresConfirmation),
                    Action::make('continue')
                        ->label(__('sn-user::user.settings.two_factor.continue'))
                        ->schema(function () {
                            return [
                                Components\OneTimeCodeInput::make('code')
                                    ->label(__('sn-user::user.settings.two_factor.code_label'))
                                    ->hiddenLabel()
                                    ->required()
                                    ->extraAttributes(['class' => 'mx-auto'], true),
                            ];
                        })
                        ->modalIcon(Heroicon::QrCode)
                        ->modalHeading(__('sn-user::user.settings.two_factor.code_label'))
                        ->modalDescription(__('sn-user::user.settings.two_factor.code_placeholder'))
                        ->modalAlignment(Alignment::Center)
                        ->modalWidth(Width::Medium)
                        ->modalCancelActionLabel(__('sn-user::user.settings.two_factor.close'))
                        ->modalSubmitActionLabel(__('sn-user::user.settings.two_factor.continue'))
                        ->modalFooterActionsAlignment(Alignment::Center)
                        ->closeModalByClickingAway(false)
                        ->cancelParentActions()     // 确认后将父级modal 也关闭
                        ->extraAttributes([
                            'class' => 'w-full',
                        ])
                        ->successNotificationTitle(__('sn-user::user.settings.two_factor.success'))
                        ->action(function (ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication, array $data) {
                            $user = Auth::guard($this->guard)->user();

                            $confirmTwoFactorAuthentication($this->module, $user, (string) $data['code'] ?? '', 'mountedActions.1.data');

                            $this->twoFactorEnabled = true;     // 用户双因素启用成功
                        })
                        ->visible($this->requiresConfirmation),
                    Text::make(__('sn-user::user.settings.two_factor.or_enter_code_manually'))
                        ->view('sn-user::filament.schema.divide'),
                    Components\TextInput::make('setup_key')
                        ->hiddenLabel()
                        ->readOnly()
                        ->default($this->manualSetupKey)
                        ->copyable(copyMessage: __('sn-user::user.settings.two_factor.copied'), copyMessageDuration: 1500),
                ];
            })
            ->modalIcon(Heroicon::QrCode)
            ->modalHeading(function () {
                return $this->requiresConfirmation
                    ? __('sn-user::user.settings.two_factor.enable_two_factor')
                    : __('sn-user::user.settings.two_factor.two_factor_enabled');
            })
            ->modalDescription(function () {
                match ($this->requiresConfirmation) {
                    true => __('sn-user::user.settings.two_factor.two_factor_enable_description'),
                    false => __('sn-user::user.settings.two_factor.two_factor_enabled_description'),
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
            ->label(__('sn-user::user.settings.two_factor.disable'))
            ->icon(Heroicon::ShieldExclamation)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('sn-user::user.settings.two_factor.disable'))
            ->modalDescription(__('sn-user::user.settings.two_factor.disable_confirmation'))
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
