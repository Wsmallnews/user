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
use Filament\Support\Facades\FilamentView;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Wsmallnews\Support\Filament\Forms\FormComponents;
use Wsmallnews\User\Actions\UpdateUserProfileInformation;
use Wsmallnews\User\Enums\Gender;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Livewire\Components\Base;
use Wsmallnews\User\Support\Utils;

class Profile extends Base implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    #[Locked]
    public string $module;

    public function mount()
    {
        $user = Auth::guard(UserConfig::getConfig($this->module, 'guard'))->user();
        $this->form->fill($user->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Text::make(__('sn-user::user.settings.profile.title')),
                FormComponents::plainImageUpload('avatar_url')
                    ->label(__('sn-user::user.settings.profile.avatar'))
                    ->avatar()
                    ->directory(Utils::getFileDirectory('avatars'))
                    ->uploadingMessage(__('sn-user::user.settings.profile.avatar_uploading')),
                Components\TextInput::make('name')
                    ->label(__('sn-user::user.settings.profile.name'))
                    ->placeholder(__('sn-user::user.settings.profile.name_placeholder'))
                    ->required(),
                Components\TextInput::make('email')
                    ->label(__('sn-user::user.settings.profile.email'))
                    ->placeholder(__('sn-user::user.settings.profile.email_placeholder'))
                    ->required()
                    ->email()
                    ->unique(ignoreRecord: true),
                Components\Radio::make('gender')
                    ->label(__('sn-user::user.settings.profile.gender'))
                    ->default(Gender::Undisclosed)
                    ->inline()
                    ->options(Gender::class),
                Components\DatePicker::make('birthday')
                    ->label(__('sn-user::user.settings.profile.birthday'))
                    ->default(null),
            ])
            ->statePath('formData');
    }

    public function getFormActions(): array
    {
        return [
            Action::make('updateProfileInformation')
                ->label(__('sn-user::user.settings.profile.submit'))
                ->submit('updateProfileInformation'),
        ];
    }

    public function updateProfileInformation(UpdateUserProfileInformation $updateUserProfileInformation): void
    {
        $formData = $this->form->getState();
        $user = Auth::guard(UserConfig::getConfig($this->module, 'guard'))->user();

        $originalEmail = $user->email;
        $updateUserProfileInformation($user, $formData);

        Notification::make()
            ->title(__('sn-user::user.settings.profile.success'))
            ->success()->send();

        if ($user->email !== $originalEmail) {
            // 自定义邮箱验证链接
            VerifyEmailNotification::createUrlUsing(function ($notifiable) {
                return UserConfig::getConfig($this->module, 'urls.verify-email-verification', fieldParams: [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]);
            });

            // 发送邮箱验证通知
            $user->sendEmailVerificationNotification();

            // 跳转到邮箱验证
            $this->redirect(UserConfig::getConfig($this->module, 'urls.verify-email') . '?type=update', FilamentView::hasSpaMode());
        }
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
            ->livewireSubmitHandler('updateProfileInformation')
            ->footer([
                $this->getFormActionsContentComponent(),
            ]);
    }

    public function getFormActionsContentComponent(): SchemaComponent
    {
        return Actions::make($this->getFormActions())
            ->fullWidth(true)
            ->key('update-profile-information-form-actions');
    }

    public function render()
    {
        return view('sn-user::livewire.components.settings.profile', []);
    }
}
