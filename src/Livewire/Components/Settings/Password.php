<?php

namespace Wsmallnews\User\Livewire\Components\Settings;

use Filament\Forms\Components;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Wsmallnews\User\Facades\UserConfig;

class Password extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    #[Locked]
    public string $module;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Text::make(__('Ensure your account is using a long, random password to stay secure')),
                Components\TextInput::make('current_password')
                    ->label('当前密码')
                    ->placeholder('请输入当前密码')
                    ->required()
                    ->rule(['current_password:' . UserConfig::getConfig($this->module, 'guard')])
                    ->password()
                    ->revealable(),
                Components\TextInput::make('password')
                    ->label('新密码')
                    ->placeholder('请输入新密码')
                    ->required()
                    ->rule(PasswordRule::default())
                    ->confirmed()
                    ->password()
                    ->revealable(),
                Components\TextInput::make('password_confirmation')
                    ->label('确认新密码')
                    ->placeholder('请确认新密码')
                    ->required()
                    ->password()
                    ->revealable()
                    ->dehydrated(false),
            ])
            ->statePath('formData');
    }

    public function updatePassword(): void
    {
        $formData = $this->form->getState();

        Auth::guard(UserConfig::getConfig($this->module, 'guard'))->user()->update([
            'password' => $formData['password'],
        ]);

        \Filament\Notifications\Notification::make()
            ->title('密码更新成功')
            ->success()->send();
    }

    public function render()
    {
        return view('sn-user::livewire.components.settings.password', []);
    }
}
