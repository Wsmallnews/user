<?php

namespace Wsmallnews\User\Livewire\Components\Auth;

use App\Models\User;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Wsmallnews\User\Facades\UserConfig;

class Login extends Component implements HasSchemas, HasActions
{
    use InteractsWithSchemas;
    use InteractsWithActions;

    public ?array $formData = [];

    #[Locked]
    public string $module;

    #[Locked]
    public ?string $userUndertakingMultiFactorAuthentication = null;


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
                Actions::make([
                    Action::make('login')
                        ->label(__('filament-panels::auth/pages/login.form.actions.authenticate.label'))
                        ->submit('login')
                ])
                ->fullWidth(true)
                ->key('form-actions'),
            ])
            ->visible(fn(): bool => blank($this->userUndertakingMultiFactorAuthentication));
    }


    public function getTwoFactorChallengeFormContentComponent(): SchemaComponent
    {
        return Form::make([EmbeddedSchema::make('twoFactorChallengeForm')])
            ->id('twoFactorChallengeForm')
            ->livewireSubmitHandler('login')
            ->footer([
                Action::make('login')
                    ->label(__('filament-panels::auth/pages/login.multi_factor.form.actions.authenticate.label'))
                    ->submit('login'),
            ])
            ->visible(fn(): bool => filled($this->userUndertakingMultiFactorAuthentication));
    }


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
                            \Filament\Actions\Action::make('forget-password')
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
                Components\OneTimeCodeInput::make('code')
                    ->label(__('filament-panels::auth/multi-factor/app/provider.login_form.code.label'))
                    ->belowContent(fn(Get $get): Action => Action::make('useRecoveryCode')
                        ->label(__('filament-panels::auth/multi-factor/app/provider.login_form.code.actions.use_recovery_code.label'))
                        ->link()
                        ->action(fn(Set $set) => $set('useRecoveryCode', true))
                        ->visible(fn(): bool => ! $get('useRecoveryCode')))
                    ->validationAttribute(__('filament-panels::auth/multi-factor/app/provider.login_form.code.validation_attribute'))
                    ->required(fn(Get $get): bool => blank($get('recoveryCode'))),
                Components\TextInput::make('recoveryCode')
                    ->label(__('filament-panels::auth/multi-factor/app/provider.login_form.recovery_code.label'))
                    ->validationAttribute(__('filament-panels::auth/multi-factor/app/provider.login_form.recovery_code.validation_attribute'))
                    ->password()
                    ->revealable()
                    ->visible(fn(Get $get): bool => filled($get('useRecoveryCode'))),
            ])
            ->statePath('formData');
    }


    public function login()
    {
        $this->ensureIsNotRateLimited();

        // 验证用户
        $user = $this->authenticate();

        if (
            UserConfig::getConfig($this->module, 'two_factor.enabled', true)        // 启用了双因素
            && $user->hasEnabledTwoFactorAuthentication($this->module)              // 用户已开启双因素
        ) {
            if (
                filled($this->userUndertakingMultiFactorAuthentication) &&
                (decrypt($this->userUndertakingMultiFactorAuthentication) === $user->getAuthIdentifier())
            ) {
                // 验证多因素认证
                $this->authenticateTwoFactor($user);
            } else {
                // 判断并且显示双因素验证界面
                $this->userUndertakingMultiFactorAuthentication = encrypt($user->getAuthIdentifier());

                $this->twoFactorChallengeForm->fill();
                return;     // 这里返回，显示界面
            }
        }

        $this->finishLogin($user);
    }

    protected function authenticate(): User 
    {
        $formData = $this->form->getState();
        $credentials = Arr::only($formData, ['password']);
        $credentials['account'] = function ($query) use ($formData) {
            $query->where(function ($query) use ($formData) {
                $query->where('email', $formData['account'])
                    ->orWhere('mobile', $formData['account']);
            });
        };

        /** @var SessionGuard $authGuard */
        $authGuard = Auth::guard(UserConfig::getConfig($this->module, 'guard'));
        $authProvider = $authGuard->getProvider();      /** @phpstan-ignore-line */

        // 当前 user model 实例
        $user = $authProvider->retrieveByCredentials($credentials);

        if ((! $user) || (! $authProvider->validateCredentials($user, $credentials))) {
            // 账号密码验证失败
            $this->userUndertakingMultiFactorAuthentication = null;

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'formData.account' => trans('auth.failed'),
            ]);
        }

        return $user;
    }


    protected function authenticateTwoFactor(User $user)
    {
        $user->


        if (
            UserConfig::getConfig($this->module, 'two_factor.enabled', true)        // 启用了双因素
            && $user->hasEnabledTwoFactorAuthentication($this->module)              // 用户已开启双因素
        ) {
            if (
                filled($this->userUndertakingMultiFactorAuthentication) &&
                (decrypt($this->userUndertakingMultiFactorAuthentication) === $user->getAuthIdentifier())
            ) {

                dd('1111111111');

                // 多因素认证 表单验证成功
                $this->twoFactorChallengeForm->validate();
            } else {
                // 判断并且显示双因素验证界面
                $this->userUndertakingMultiFactorAuthentication = encrypt($user->getAuthIdentifier());

                $this->twoFactorChallengeForm->fill();
                return;     // 这里返回，显示界面
            }
        }
    }


    protected function finishLogin () 
    {
        $formData = $this->form->getState();
        $credentials = Arr::only($formData, ['password']);
        $credentials['account'] = function ($query) use ($formData) {
            $query->where(function ($query) use ($formData) {
                $query->where('email', $formData['account'])
                    ->orWhere('mobile', $formData['account']);
            });
        };

        /** @var SessionGuard $authGuard */
        $authGuard = Auth::guard(UserConfig::getConfig($this->module, 'guard'));
        $authProvider = $authGuard->getProvider();
        /** @phpstan-ignore-line */

        // 账号密码验证成功， 多因素认证验证成功
        if (! $authGuard->attemptWhen($credentials, function (User $user): bool {
            // 这里可以加入其他条件， 密码已经在上面验证过了
            return true;
        }, $formData['remember'] ?? false)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'formData.account' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // 登录成功，重新生成 session id
        Session::regenerate();

        \Filament\Notifications\Notification::make()
            ->title('登录成功')
            ->success()->send();

        // 退回上个url
        $this->redirectIntended(UserConfig::getConfig($this->module, 'urls.index'), FilamentView::hasSpaMode());
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'formData.account' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        $formData = $this->form->getState();

        return Str::transliterate(Str::lower($formData['account']) . '|' . request()->ip());
    }

    public function render()
    {
        return view('sn-user::livewire.components.auth.login', []);
    }
}
