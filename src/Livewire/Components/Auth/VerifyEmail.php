<?php

namespace Wsmallnews\User\Livewire\Components\Auth;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Locked;
use Wsmallnews\User\Actions\Logout;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Livewire\Components\Base;

class VerifyEmail extends Base implements HasSchemas
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
                Text::make(__('sn-user::user.auth.verify_email.register_description'))->visible($this->type === 'register'),
                Text::make(__('sn-user::user.auth.verify_email.update_description'))->visible($this->type == 'update'),
                Text::make(__('sn-user::user.auth.verify_email.check_description'))->visible($this->type == 'check'),
            ])
            ->statePath('formData');
    }

    public function getFormActions(): array
    {
        return [
            Action::make('sendVerification')
                ->label($this->type === 'register' || $this->type === 'update' || session('status') ? __('sn-user::user.auth.verify_email.resend') : __('sn-user::user.auth.verify_email.send'))
                ->submit('sendVerification'),
        ];
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

        Session::flash('status', __('sn-user::user.auth.verify_email.sent'));
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        // 退出登录
        $logout($this->module);

        // 通知用户退出登录
        Notification::make()
            ->title(__('sn-user::user.auth.verify_email.logged_out'))
            ->success()->send();

        $this->redirect(UserConfig::getConfig($this->module, 'urls.index'), FilamentView::hasSpaMode());
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
            ->livewireSubmitHandler('sendVerification')
            ->footer([
                $this->getFormActionsContentComponent(),
            ]);
    }

    public function getFormActionsContentComponent(): SchemaComponent
    {
        return Actions::make($this->getFormActions())
            ->fullWidth(true)
            ->key('verify-email-form-actions');
    }

    public function render()
    {
        return view('sn-user::livewire.components.auth.verify-email', []);
    }
}
