<?php

namespace Wsmallnews\User\Livewire\Components\Auth;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Wsmallnews\User\Actions\AttemptToAuthenticate;
use Wsmallnews\User\Actions\AttemptTwoFactorAuthenticate;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Livewire\Components\Base;

class Login extends Base implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $formData = [];

    #[Locked]
    public string $module;

    #[Locked]
    public ?string $userUndertakingMultiFactorAuthentication = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\TextInput::make('account')
                    ->label(__('sn-user::user.auth.login.account'))
                    ->placeholder(__('sn-user::user.auth.login.account_placeholder'))
                    ->required(),
                Components\TextInput::make('password')
                    ->label(__('sn-user::user.auth.login.password'))
                    ->placeholder(__('sn-user::user.auth.login.password_placeholder'))
                    ->required()
                    ->password()
                    ->revealable()
                    ->afterLabel(function () {
                        $forgotPasswordUrl = UserConfig::getConfig($this->module, 'urls.forgot-password');

                        return $forgotPasswordUrl ?
                            Action::make('forget-password')
                                ->label(__('sn-user::user.auth.login.forgot_password'))
                                ->url((string) $forgotPasswordUrl)
                            : null;
                    }),
                Components\Checkbox::make('remember')->label(__('sn-user::user.auth.login.remember'))->inline(),
            ])
            ->statePath('formData');
    }

    public function twoFactorChallengeForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Hidden::make('useRecoveryCode')
                    ->default(false),

                Components\OneTimeCodeInput::make('code')
                    ->label(__('sn-user::user.auth.login.two_factor_code'))
                    ->belowContent(
                        fn (Get $get): Action => Action::make('useRecoveryCode')
                            ->label(__('sn-user::user.auth.login.use_recovery_code'))
                            ->link()
                            ->action(fn (Set $set) => $set('useRecoveryCode', true))
                    )
                    ->validationAttribute(__('sn-user::user.auth.login.two_factor_code_validation_attribute'))
                    ->required(fn (Get $get): bool => ! (bool) $get('useRecoveryCode'))
                    ->visible(fn (Get $get): bool => ! (bool) $get('useRecoveryCode')),

                Components\TextInput::make('recoveryCode')
                    ->label(__('sn-user::user.auth.login.use_recovery_code'))
                    ->belowContent(
                        fn (Get $get): Action => Action::make('useCode')
                            ->label(__('sn-user::user.auth.login.use_authenticator_code'))
                            ->link()
                            ->action(fn (Set $set) => $set('useRecoveryCode', false))
                    )
                    ->validationAttribute(__('sn-user::user.auth.login.recovery_code_validation_attribute'))
                    ->password()
                    ->revealable()
                    ->required(fn (Get $get): bool => (bool) $get('useRecoveryCode'))
                    ->visible(fn (Get $get): bool => (bool) $get('useRecoveryCode')),
            ])
            ->statePath('formData.twoFactor');
    }

    public function getFormActions()
    {
        return [
            Action::make('login')
                ->label(__('sn-user::user.auth.login.submit'))
                ->submit('login'),
        ];
    }

    public function getTwoFactorChallengeFormActions()
    {
        return [
            Action::make('login')
                ->label(__('sn-user::user.auth.login.two_factor_submit'))
                ->submit('login'),
        ];
    }

    public function login()
    {
        $formData = $this->form->getState();
        $attemptToAuthenticate = new AttemptToAuthenticate($this->module, $formData);

        // 登录限制
        if (! $attemptToAuthenticate->ensureIsNotRateLimited()) {
            $seconds = $attemptToAuthenticate->lockSecond();

            throw ValidationException::withMessages([
                'formData.account' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // 检索用户
        $user = $attemptToAuthenticate->retrieveUser();

        if (! $user || ! $attemptToAuthenticate->validateCredentials($user)) {
            // 账号密码验证失败
            $this->userUndertakingMultiFactorAuthentication = null;

            $this->addError('formData.account', trans('auth.failed'));

            return;
        }

        if (
            UserConfig::getConfig($this->module, 'two_factor.enabled', true)        // 启用了双因素
            && $user->hasEnabledTwoFactorAuthentication($this->module)              // 用户已开启双因素
        ) {
            if (
                filled($this->userUndertakingMultiFactorAuthentication) &&
                (decrypt($this->userUndertakingMultiFactorAuthentication) === $user->getAuthIdentifier())
            ) {
                $twoFactorChallengeForm = $this->twoFactorChallengeForm->getState();
                $attemptTwoFactorAuthenticate = new AttemptTwoFactorAuthenticate($this->module, $twoFactorChallengeForm);

                // 验证多因素认证
                if (! $attemptTwoFactorAuthenticate($user)) {
                    $recoveryCode = $twoFactorChallengeForm['recoveryCode'] ?? null;
                    if ($recoveryCode) {
                        $this->addError('formData.twoFactor.recoveryCode', __('sn-user::user.auth.login.invalid_recovery_code'));
                    } else {
                        $this->addError('formData.twoFactor.code', __('sn-user::user.auth.login.two_factor_failed'));
                    }

                    return;
                }
            } else {
                // 判断并且显示双因素验证界面
                $this->userUndertakingMultiFactorAuthentication = encrypt($user->getAuthIdentifier());

                $this->twoFactorChallengeForm->fill();

                return;     // 这里返回，显示界面
            }
        }

        // 完成登录
        $attemptToAuthenticate->finishLogin($user);

        Notification::make()
            ->title(__('sn-user::user.auth.login.success'))
            ->success()->send();

        // 退回上个url
        $this->redirectIntended(UserConfig::getConfig($this->module, 'urls.index'), FilamentView::hasSpaMode());
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
                $this->getTwoFactorChallengeFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): SchemaComponent
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('login')
            ->footer([
                $this->getFormActionsContentComponent(),
            ])
            ->visible(fn (): bool => blank($this->userUndertakingMultiFactorAuthentication));
    }

    public function getFormActionsContentComponent(): SchemaComponent
    {
        return Actions::make($this->getFormActions())
            ->fullWidth(true)
            ->key('login-form-actions');
    }

    public function getTwoFactorChallengeFormContentComponent(): SchemaComponent
    {
        return Form::make([EmbeddedSchema::make('twoFactorChallengeForm')])
            ->id('twoFactorChallengeForm')
            ->livewireSubmitHandler('login')
            ->footer([
                $this->getTwoFactorChallengeFormActionsContentComponent(),
            ])
            ->visible(fn (): bool => filled($this->userUndertakingMultiFactorAuthentication));
    }

    public function getTwoFactorChallengeFormActionsContentComponent(): SchemaComponent
    {
        return Actions::make($this->getTwoFactorChallengeFormActions())
            ->fullWidth(true)
            ->key('two-factor-challenge-form-actions');
    }

    public function render()
    {
        return view('sn-user::livewire.components.auth.login', []);
    }
}
