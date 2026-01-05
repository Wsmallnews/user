<?php

namespace Wsmallnews\User\Livewire\Components\Auth;

use Filament\Forms\Components;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Wsmallnews\User\Facades\AuthsConfig;

class TwoFactorAuthenticated extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    public string $module;

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
        return view('sn-user::livewire.auth.confirm-password', []);
    }
}
