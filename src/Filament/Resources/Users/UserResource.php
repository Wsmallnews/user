<?php

namespace Wsmallnews\User\Filament\Resources\Users;

use Wsmallnews\Support\Filament\Concerns\CanBeConfigured;
use Wsmallnews\Support\Filament\Resources\ResourceConfiguration;
use Wsmallnews\User\Filament\Resources\Users\Pages\ListUsers;
use Wsmallnews\User\UserPlugin;

final class UserResource extends BaseResource
{
    use CanBeConfigured;

    protected static ?string $configurationClass = ResourceConfiguration::class;

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
        ];
    }

    public static function getEssentialsPlugin(): ?UserPlugin
    {
        return UserPlugin::get();
    }
}
