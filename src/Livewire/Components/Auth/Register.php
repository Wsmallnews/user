<?php

namespace Wsmallnews\User\Livewire\Components\Auth;

use App\Models\User;
use Filament\Forms\Components;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Wsmallnews\User\Facades\AuthsConfig;

class Register extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    public string $module;

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
                    ->rule(Password::default())
                    ->same('password_confirmation')
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

    public function register(): void
    {
        $formData = $this->form->getState();

        $formData['password'] = Hash::make($formData['password']);

        try {
            $user = User::create($formData);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'formData.email' => '该邮箱已注册, 请直接登录',
            ]);
        }

        event(new Registered($user));

        Auth::guard(AuthsConfig::getConfig($this->module, 'guard'))->login($user);

        $this->redirect(AuthsConfig::getConfig($this->module, 'urls.user-index'), FilamentView::hasSpaMode());
    }

    public function render()
    {
        return view('sn-user::livewire.auth.register', []);
    }
}
