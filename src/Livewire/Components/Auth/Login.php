<?php

namespace Wsmallnews\User\Livewire\Components\Auth;

use Filament\Forms\Components;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Wsmallnews\User\Facades\AuthsConfig;

class Login extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    public string $module;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\TextInput::make('account')
                    ->label('账号')
                    ->placeholder('请输入邮箱或手机号')
                    ->required()
                    ->email(),
                Components\TextInput::make('password')
                    ->label('密码')
                    ->placeholder('请输入密码')
                    ->required()
                    ->password()
                    ->revealable(),

                Components\Checkbox::make('remember')->label('记住我')->inline(),
            ])
            ->statePath('formData');
    }

    public function login()
    {
        $this->ensureIsNotRateLimited();

        $this->authenticate();

        Session::regenerate();

        // 退回上个url
        $this->redirectIntended(AuthsConfig::getConfig($this->module, 'urls.index'));
    }

    protected function authenticate(): void
    {
        $formData = $this->form->getState();
        $credentials = Arr::only($formData, ['password']);
        $credentials['account'] = function ($query) use ($formData) {
            $query->where(function ($query) use ($formData) {
                $query->where('email', $formData['account'])
                    ->orWhere('mobile', $formData['account']);
            });
        };

        if (! Auth::guard(AuthsConfig::getConfig($this->module, 'guard'))->attempt($credentials, $formData['remember'] ?? false)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.account' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
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
        return view('sn-user::livewire.auth.login', []);
    }
}
