<?php

namespace Wsmallnews\User\Livewire\Components\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Wsmallnews\User\Actions\CreateNewUser;
use Wsmallnews\User\Facades\UserConfig;

class Register extends Component implements HasSchemas
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
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            $this->addError('formData.email', '该邮箱已注册, 请直接登录');

            return;
        }

        // 用户注册事件
        event(new Registered($user));

        \Filament\Notifications\Notification::make()
            ->title('注册成功')
            ->success()->send();

        // 自定义邮箱验证链接
        VerifyEmailNotification::createUrlUsing(function ($notifiable) {
            return UserConfig::getConfig($this->module, 'urls.verify-email-verification', fieldParams: [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]);
        });

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
