<?php

namespace Wsmallnews\User\Filament\Resources\Users\Schemas;

use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Wsmallnews\Support\Filament\Forms\FormComponents;
use Wsmallnews\User\Enums\Gender;
use Wsmallnews\User\Enums\Status;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...static::forms(),
            ]);
    }

    public static function forms(): array
    {
        return [
            Schemas\Components\Section::make(__('sn-user::user.user_resource.table.user_info'))->schema([
                FormComponents::localImageUpload('avatar_url')
                    ->label(__('sn-user::user.settings.profile.avatar'))
                    ->avatar()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('username')
                    ->label(__('sn-user::user.user_resource.table.username'))
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('name')
                    ->label(__('sn-user::user.user_resource.table.name'))
                    ->required()
                    ->maxLength(10),
                // Forms\Components\TextInput::make('mobile')
                //     ->label(__('sn-user::user.user_resource.table.mobile'))
                //     ->tel()
                //     ->maxLength(20),
                Forms\Components\TextInput::make('email')
                    ->label(__('sn-user::user.user_resource.table.email'))
                    ->email()
                    ->maxLength(255),
                Forms\Components\ToggleButtons::make('gender')
                    ->label(__('sn-user::user.settings.profile.gender'))
                    ->options(Gender::class)
                    ->default(Gender::Undisclosed)
                    ->required()->grouped(),
                Forms\Components\DatePicker::make('birthday')
                    ->label(__('sn-user::user.settings.profile.birthday'))
                    ->format('Y-m-d')
                    ->displayFormat('Y-m-d'),
                Forms\Components\ToggleButtons::make('status')
                    ->label(__('sn-member::member.member_resource.table.status'))
                    ->options(Status::class)
                    ->default(Status::Normal)
                    ->required()->grouped(),
            ])->columns(2)->columnSpanFull(),
        ];
    }
}
