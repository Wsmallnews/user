<?php

namespace Wsmallnews\User\Components\Auth;

use Filament\Forms\Components;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Wsmallnews\User\Enums\Status;

class Register extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('email')
                    ->required()
                    ->email(),
                Components\TextInput::make('password')
                    ->required()
                    ->password()
                    ->revealable(),
                Components\Checkbox::make('remember')->inline(),
            ])
            ->statePath('formData');
    }

    public function login(): void
    {
        $this->ensureIsNotRateLimited();

        $this->authenticate();

        Session::regenerate();

        $this->dispatch('login-success');
    }

    protected function authenticate(): void
    {
        $formData = $this->form->getState();

        if (! Auth::attemptWhen(Arr::only($formData, ['email', 'password']), function ($user) {
            return $user->status === Status::Normal->value;
        }, $formData['remember'])) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'formData.email' => trans('auth.failed'),
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
            'formData.email' => trans('auth.throttle', [
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

        return Str::transliterate(Str::lower($formData['email']) . '|' . request()->ip());
    }

    public function render()
    {
        return view('sn-user::livewire.auth.login', []);
    }
}
