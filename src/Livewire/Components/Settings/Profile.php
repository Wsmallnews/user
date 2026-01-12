<?php

namespace Wsmallnews\User\Livewire\Components\Settings;

use Filament\Actions\Action;
use Filament\Forms\Components;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Wsmallnews\User\Facades\UserConfig;
use Wsmallnews\User\Support\Utils;
use Wsmallnews\Support\Support\Utils as SupportUtils;

class Profile extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $formData = [];

    #[Locked]
    public string $module;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Text::make(__('Update your profile information')),
                Components\FileUpload::make('avatar_url')
                    ->label(__('Avatar'))
                    ->avatar()
                    ->disk(SupportUtils::getFilesystemDisk())
                    ->directory(Utils::getFileDirectory('avatars'))
                    ->visibility('public')
                    ->openable()
                    ->downloadable()
                    ->uploadingMessage('头像上传中...')
                    ->imagePreviewHeight('100'),
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
            ])
            ->statePath('formData');
    }

    public function updateProfileInformation(): void
    {
        $formData = $this->form->getState();

        $user = Auth::guard(UserConfig::getConfig($this->module, 'guard'))->user();

        $user->fill($formData);

        if ($user->isDirty('email')) {
            // 修改了邮箱，重置验证状态
            $user->email_verified_at = null;
        }

        $user->save();

        \Filament\Notifications\Notification::make()
            ->title('个人信息更新成功')
            ->success()->send();
    }

    public function render()
    {
        return view('sn-user::livewire.settings.profile', []);
    }
}
