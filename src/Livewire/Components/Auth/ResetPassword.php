<?php

namespace Wsmallnews\User\Livewire\Components\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Locked;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Livewire\Components\Base;

class ResetPassword extends Base implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    #[Locked]
    public string $module;

    public function mount(): void
    {
        $this->form->fill([
            'token' => request()->route('token'),
            'email' => request()->query('email'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Hidden::make('token')
                    ->required(),
                Components\TextInput::make('email')
                    ->label(__('sn-user::user.auth.reset_password.email'))
                    ->placeholder(__('sn-user::user.auth.reset_password.email_placeholder'))
                    ->required()
                    ->email(),
                Components\TextInput::make('password')
                    ->label(__('sn-user::user.auth.reset_password.password'))
                    ->placeholder(__('sn-user::user.auth.reset_password.password_placeholder'))
                    ->required()
                    ->rule(Rules\Password::default())
                    ->same('password_confirmation')
                    ->password()
                    ->revealable(),
                Components\TextInput::make('password_confirmation')
                    ->label(__('sn-user::user.auth.reset_password.password_confirmation'))
                    ->placeholder(__('sn-user::user.auth.reset_password.password_confirmation_placeholder'))
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
            Action::make('resetPassword')
                ->label(__('sn-user::user.auth.reset_password.submit'))
                ->submit('resetPassword'),
        ];
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $formData = $this->form->getState();

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        // Password::reset() is Illuminate\Auth\Passwords\PasswordBroker::reset()
        $status = Password::reset(
            Arr::only($formData, ['email', 'password', 'token']),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('formData.email', __($status));

            return;
        }

        Notification::make()
            ->title(__($status))
            ->success()->send();

        $this->redirect(UserConfig::getConfig($this->module, 'urls.login'), FilamentView::hasSpaMode());
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
            ->livewireSubmitHandler('resetPassword')
            ->footer([
                $this->getFormActionsContentComponent(),
            ]);
    }

    public function getFormActionsContentComponent(): SchemaComponent
    {
        return Actions::make($this->getFormActions())
            ->fullWidth(true)
            ->key('reset-password-form-actions');
    }

    public function render()
    {
        return view('sn-user::livewire.components.auth.reset-password', []);
    }
}
