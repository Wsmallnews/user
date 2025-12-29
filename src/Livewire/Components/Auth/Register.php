<?php

namespace Wsmallnews\User\Livewire\Components\Auth;

use Filament\Forms\Components;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Register extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    public string $module;

    public string $backRoute;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\TextInput::make('name')
                    ->label('昵称')
                    ->placeholder('请输入昵称')
                    ->required(),
                Components\TextInput::make('email')
                    ->label('邮箱')
                    ->placeholder('请输入邮箱')
                    ->required()
                    ->email(),
                Components\TextInput::make('password')
                    ->label('密码')
                    ->placeholder('请输入密码')
                    ->required()
                    ->same('passwordConfirmation')
                    ->password()
                    ->revealable(),
                Components\TextInput::make('password_confirmation')
                    ->label('确认密码')
                    ->placeholder('请确认密码')
                    ->required()
                    ->password()
                    ->revealable()
                    ->dehydrated(false),
            ])
            ->statePath('formData');
    }

    public function login(): RedirectResponse
    {
        $this->ensureIsNotRateLimited();

        $this->authenticate();

        Session::regenerate();

        return redirect()->route($this->backRoute);
    }

    protected function authenticate(): void
    {
        $formData = $this->form->getState();

        if (! Auth::attempt(Arr::only($formData, ['account', 'password']), $formData['remember'] ?? false)) {
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
        return view('sn-user::livewire.auth.register', []);
    }
}
