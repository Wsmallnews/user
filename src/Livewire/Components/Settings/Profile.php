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
use Livewire\Component;
use Wsmallnews\Support\Filament\Forms\FormComponents;
use Wsmallnews\User\Actions\UpdateUserProfileInformation;
use Wsmallnews\User\Enums\Gender;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Support\Utils;

class Profile extends Component implements HasSchemas
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
                Text::make(__('Update your profile information')),
                FormComponents::localImageUpload('avatar_url')
                    ->label(__('Avatar'))
                    ->avatar()
                    ->directory(Utils::getFileDirectory('avatars'))
                    ->uploadingMessage('头像上传中...'),
                Components\TextInput::make('name')
                    ->label(__('Name'))
                    ->placeholder('请输入你的名称')
                    ->required(),
                Components\TextInput::make('email')
                    ->label('邮箱')
                    ->placeholder('请输入邮箱')
                    ->required()
                    ->email()
                    ->unique(ignoreRecord: true),
                Components\Radio::make('gender')
                    ->label('性别')
                    ->default(Gender::Undisclosed)
                    ->inline()
                    ->options(Gender::class),
                Components\DatePicker::make('birthday')
                    ->label('生日')
                    ->default(null),
            ])
            ->statePath('formData');
    }

    public function getFormActions(): array
    {
        return [
            Action::make('updateProfileInformation')
                ->label('更新个人信息')
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
            ->title('个人信息更新成功')
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
