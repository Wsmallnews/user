<?php

namespace Wsmallnews\User\Livewire\Components\Settings;

use Filament\Actions\Action;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Locked;
use Wsmallnews\User\Actions\UpdateUserPassword;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Livewire\Components\Base;

class Password extends Base implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    #[Locked]
    public string $module;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Text::make(__('sn-user::user.settings.password.title')),
                Components\TextInput::make('current_password')
                    ->label(__('sn-user::user.settings.password.current_password'))
                    ->placeholder(__('sn-user::user.settings.password.current_password_placeholder'))
                    ->required()
                    ->rule(['current_password:' . UserConfig::getConfig($this->module, 'guard')])
                    ->password()
                    ->revealable(),
                Components\TextInput::make('password')
                    ->label(__('sn-user::user.settings.password.password'))
                    ->placeholder(__('sn-user::user.settings.password.password_placeholder'))
                    ->required()
                    ->rule(PasswordRule::default())
                    ->confirmed()
                    ->password()
                    ->revealable(),
                Components\TextInput::make('password_confirmation')
                    ->label(__('sn-user::user.settings.password.password_confirmation'))
                    ->placeholder(__('sn-user::user.settings.password.password_confirmation_placeholder'))
                    ->required()
                    ->password()
                    ->revealable()
                    ->dehydrated(false),
            ])
            ->statePath('formData');
    }

    public function getFormActions(): array
    {
        return [
            Action::make('updatePassword')
                ->label(__('sn-user::user.settings.password.submit'))
                ->submit('updatePassword'),
        ];
    }

    public function updatePassword(UpdateUserPassword $updateUserPassword): void
    {
        $formData = $this->form->getState();
        $user = Auth::guard(UserConfig::getConfig($this->module, 'guard'))->user();

        $updateUserPassword($user, $formData);

        Notification::make()
            ->title(__('sn-user::user.settings.password.success'))
            ->success()->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): SchemaComponent
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('updatePassword')
            ->footer([
                $this->getFormActionsContentComponent(),
            ]);
    }

    public function getFormActionsContentComponent(): SchemaComponent
    {
        return Actions::make($this->getFormActions())
            ->fullWidth(true)
            ->key('update-password-form-actions');
    }

    public function render()
    {
        return view('sn-user::livewire.components.settings.password', []);
    }
}
