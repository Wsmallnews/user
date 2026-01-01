<?php

namespace Wsmallnews\User\Livewire\Components\Auth;

use Filament\Schemas\Components\Text;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Wsmallnews\User\Facades\AuthsConfig;
use Wsmallnews\User\Livewire\Actions\Logout;


class VerifyEmail extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    public string $module;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Text::make('感谢您的注册！在开始之前，您能否点击我们刚刚发送给您的电子邮件中的链接，以验证您的电子邮件地址？如果您没有收到该电子邮件，我们很乐意为您重新发送一封。')
            ])
            ->statePath('formData');
    }


    public function sendVerification(): void
    {
        $guard = AuthsConfig::getConfig($this->module, 'guard');
        if (Auth::guard($guard)->user()->hasVerifiedEmail()) {
            $this->redirectIntended(AuthsConfig::getConfig($this->module, 'urls.profile'), FilamentView::hasSpaMode());

            return;
        }

        // 自定义邮箱验证链接
        VerifyEmailNotification::createUrlUsing(function ($notifiable) {
            return AuthsConfig::getConfig($this->module, 'urls.verify-email-verification', fieldParams: [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]);
        });

        Auth::guard($guard)->user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }


    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        // 退出登录
        $logout(AuthsConfig::getConfig($this->module, 'guard'));

        $this->redirect(AuthsConfig::getConfig($this->module, 'urls.index'), FilamentView::hasSpaMode());
    }


    public function render()
    {
        return view('sn-user::livewire.auth.verify-email', []);
    }
}
