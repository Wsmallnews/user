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
use Livewire\Attributes\Locked;
use Livewire\Component;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Livewire\Actions\Logout;

class VerifyEmail extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    public string $type = 'check';

    #[Locked]
    public string $module;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Text::make('感谢您的注册！在开始之前，您能否点击我们刚刚发送给您的电子邮件中的链接，以验证您的电子邮件地址？如果您没有收到该电子邮件，我们很乐意为您重新发送一封。')->visible($this->type === 'register'),
                Text::make('您更新了您的电子邮箱，在继续之前，您能否点击我们刚刚发送给您的电子邮件中的链接，以验证您的电子邮件地址？如果您没有收到该电子邮件，我们很乐意为您重新发送一封。')->visible($this->type == 'update'),
                Text::make('您的电子邮箱还未验证，在继续之前，请先发送一封电子邮件，然后点击电子邮件中的链接，以验证您的电子邮件地址')->visible($this->type == 'check'),
            ])
            ->statePath('formData');
    }

    public function sendVerification(): void
    {
        $guard = UserConfig::getConfig($this->module, 'guard');
        if (Auth::guard($guard)->user()->hasVerifiedEmail()) {
            $this->redirectIntended(UserConfig::getConfig($this->module, 'urls.profile'), FilamentView::hasSpaMode());

            return;
        }

        // 自定义邮箱验证链接
        VerifyEmailNotification::createUrlUsing(function ($notifiable) {
            return UserConfig::getConfig($this->module, 'urls.verify-email-verification', fieldParams: [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]);
        });

        Auth::guard($guard)->user()->sendEmailVerificationNotification();

        Session::flash('status', '验证邮件已发送');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        // 退出登录
        $logout(UserConfig::getConfig($this->module, 'guard'));

        // 通知用户退出登录
        \Filament\Notifications\Notification::make()
            ->title('您已退出登录')
            ->success()->send();

        $this->redirect(UserConfig::getConfig($this->module, 'urls.index'), FilamentView::hasSpaMode());
    }

    public function render()
    {
        return view('sn-user::livewire.auth.verify-email', []);
    }
}
