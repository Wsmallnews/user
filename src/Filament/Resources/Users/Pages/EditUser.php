<?php

namespace Wsmallnews\User\Filament\Resources\Users\Pages;

use Filament\Resources\Pages\EditRecord;
use Wsmallnews\User\Filament\Resources\Users\UserResource;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
}
