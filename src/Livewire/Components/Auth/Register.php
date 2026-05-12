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
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Locked;
use Wsmallnews\User\Actions\CreateNewUser;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Livewire\Components\Base;

class Register extends Base implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    #[Locked]
    public string $module;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\TextInput::make('name')
                    ->label(__('sn-user::user.auth.register.name'))
                    ->placeholder(__('sn-user::user.auth.register.name_placeholder'))
                    ->required(),
                Components\TextInput::make('email')
                    ->label(__('sn-user::user.auth.register.email'))
                    ->placeholder(__('sn-user::user.auth.register.email_placeholder'))
                    ->required()
                    ->email(),
                Components\TextInput::make('password')
                    ->label(__('sn-user::user.auth.register.password'))
                    ->placeholder(__('sn-user::user.auth.register.password_placeholder'))
                    ->required()
                    ->rule(Password::default())
                    ->same('password_confirmation')
                    ->password()
                    ->revealable(),
                Components\TextInput::make('password_confirmation')
                    ->label(__('sn-user::user.auth.register.password_confirmation'))
                    ->placeholder(__('sn-user::user.auth.register.password_confirmation_placeholder'))
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
            Action::make('register')
                ->label(__('filament-panels::auth/pages/register.form.actions.register.label'))
                ->submit('register'),
        ];
    }

    public function register(CreateNewUser $createNewUser): void
    {
        $formData = $this->form->getState();

        try {
            $user = $createNewUser($formData);
        } catch (UniqueConstraintViolationException) {
            $this->addError('formData.email', __('sn-user::user.auth.register.email_exists'));

            return;
        }

        // 自定义邮箱验证链接
        VerifyEmailNotification::createUrlUsing(function ($notifiable) {
            return UserConfig::getConfig($this->module, 'urls.verify-email-verification', fieldParams: [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]);
        });
        // 用户注册事件 （ 这里会自动发送邮件，所以邮箱联接要提前设置好）
        event(new Registered($user));

        Notification::make()
            ->title(__('sn-user::user.auth.register.success'))
            ->success()->send();

        Auth::guard(UserConfig::getConfig($this->module, 'guard'))->login($user);

        // 跳转到邮箱验证
        $this->redirect(UserConfig::getConfig($this->module, 'urls.verify-email') . '?type=register', FilamentView::hasSpaMode());
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
            ->livewireSubmitHandler('register')
            ->footer([
                $this->getFormActionsContentComponent(),
            ]);
    }

    public function getFormActionsContentComponent(): SchemaComponent
    {
        return Actions::make($this->getFormActions())
            ->fullWidth(true)
            ->key('register-form-actions');
    }

    public function render()
    {
        return view('sn-user::livewire.components.auth.register', []);
    }
}
