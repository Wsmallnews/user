<?php

namespace Wsmallnews\User\Filament\Resources\Users;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Wsmallnews\User\Filament\Resources\Users\Schemas\UserForm;
use Wsmallnews\User\Filament\Resources\Users\Tables\UserTable;
use Wsmallnews\User\Support\Utils;

abstract class BaseResource extends Resource
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUser;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::User;

    protected static ?string $slug = 'users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function getModel(): string
    {
        return Utils::getUserModel();
    }

    public static function getModelLabel(): string
    {
        return static::$modelLabel ?? __('sn-user::user.user_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return static::$pluralModelLabel ?? __('sn-user::user.user_resource.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? __('sn-user::user.user_resource.navigation_label');
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return static::$navigationGroup ?? __('sn-user::user.global_default.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserTable::configure($table);
    }
}
