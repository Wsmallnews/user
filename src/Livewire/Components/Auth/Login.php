<?php

namespace Wsmallnews\User\Livewire\Components\Auth;

use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;


use Filament\Forms\Components;
use Filament\Forms\Form;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Wsmallnews\User\Enums\Status;
use Wsmallnews\User\User;

class Login extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];


    public function form(Schema  $schema): Schema
    {
        return $schema
            ->components([
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

    public function login(): RedirectResponse
    {
        $this->ensureIsNotRateLimited();

        $this->authenticate();

        Session::regenerate();

        return redirect()->route('tenant.index');
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
