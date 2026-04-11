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
use Livewire\Component;
use Wsmallnews\User\Actions\AttemptToAuthenticate;
use Wsmallnews\User\Actions\AttemptTwoFactorAuthenticate;
use Wsmallnews\User\Facades\UserConfig;

class Login extends Component implements HasActions, HasSchemas
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
                    ->label('账号')
                    ->placeholder('请输入邮箱或手机号')
                    ->required(),
                Components\TextInput::make('password')
                    ->label('密码')
                    ->placeholder('请输入密码')
                    ->required()
                    ->password()
                    ->revealable()
                    ->afterLabel(function () {
                        $forgotPasswordUrl = UserConfig::getConfig($this->module, 'urls.forgot-password');

                        return $forgotPasswordUrl ?
                            Action::make('forget-password')
                                ->label('忘记密码？')
                                ->url((string) $forgotPasswordUrl)
                        : null;
                    }),
                Components\Checkbox::make('remember')->label('记住我')->inline(),
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
                    ->label(__('filament-panels::auth/multi-factor/app/provider.login_form.code.label'))
                    ->belowContent(
                        fn (Get $get): Action => Action::make('useRecoveryCode')
                            ->label(__('filament-panels::auth/multi-factor/app/provider.login_form.code.actions.use_recovery_code.label'))
                            ->link()
                            ->action(fn (Set $set) => $set('useRecoveryCode', true))
                    )
                    ->validationAttribute(__('filament-panels::auth/multi-factor/app/provider.login_form.code.validation_attribute'))
                    ->required(fn (Get $get): bool => ! (bool) $get('useRecoveryCode'))
                    ->visible(fn (Get $get): bool => ! (bool) $get('useRecoveryCode')),

                Components\TextInput::make('recoveryCode')
                    ->label('使用恢复码')
                    ->belowContent(
                        fn (Get $get): Action => Action::make('useCode')
                            ->label('使用 Authenticator App 密码')
                            ->link()
                            ->action(fn (Set $set) => $set('useRecoveryCode', false))
                    )
                    ->validationAttribute(__('filament-panels::auth/multi-factor/app/provider.login_form.recovery_code.validation_attribute'))
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
                ->label('登录')
                ->submit('login'),
        ];
    }

    public function getTwoFactorChallengeFormActions()
    {
        return [
            Action::make('login')
                ->label('验证登录')
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
                        $this->addError('formData.twoFactor.recoveryCode', '恢复码不正确或已失效');
                    } else {
                        $this->addError('formData.twoFactor.code', '双因素认证失败');
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
            ->title('登录成功')
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
