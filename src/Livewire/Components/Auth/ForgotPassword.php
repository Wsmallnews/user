<?php

namespace Wsmallnews\User\Livewire\Components\Auth;

use Filament\Forms\Components;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Wsmallnews\User\Facades\AuthsConfig;

class ForgotPassword extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    #[Locked]
    public string $module;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\TextInput::make('email')
                    ->label('邮箱')
                    ->aboveLabel('忘记密码了？没关系。只需告诉我们您注册时使用的电子邮箱地址，我们将向您发送一个密码重置链接，通过该链接您即可设置一个新密码')
                    ->placeholder('请输入邮箱')
                    ->required()
                    ->email(),
            ])
            ->statePath('formData');
    }

    public function sendPasswordResetLink(): void
    {
        $formData = $this->form->getState();

        // 自定义密码重置链接
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return AuthsConfig::getConfig($this->module, 'urls.reset-password', fieldParams: [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            Arr::only($formData, ['email'])
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('formData.email', __($status));

            return;
        }

        // 清空表单
        $this->form->fill();

        session()->flash('status', __($status));
    }

    public function render()
    {
        return view('sn-user::livewire.auth.forgot-password', []);
    }
}
