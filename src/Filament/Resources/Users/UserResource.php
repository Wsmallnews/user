<?php

namespace Wsmallnews\User\Filament\Resources\Users;

use Wsmallnews\Support\Filament\Concerns\CanBeConfigured;
use Wsmallnews\User\Filament\Resources\Users\Pages\EditUser;
use Wsmallnews\User\Filament\Resources\Users\Pages\ListUsers;

final class UserResource extends BaseResource
{
    use CanBeConfigured;

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'edit' => EditUser::route('/{record}'),
        ];
    }
}
