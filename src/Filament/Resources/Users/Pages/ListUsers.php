<?php

namespace Wsmallnews\User\Filament\Resources\Users\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Wsmallnews\User\Enums\Status;
use Wsmallnews\User\Filament\Resources\Users\UserResource;
use Wsmallnews\User\Support\Utils;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label(__('sn-user::user.user_resource.tabs.all'))
                ->badge(fn () => $this->getCount()),
            'normal' => Tab::make()
                ->label(Status::Normal->getLabel())
                ->badge(fn () => $this->getCount(Status::Normal))
                ->modifyQueryUsing(fn ($query) => $query->where('status', Status::Normal)),
            'disabled' => Tab::make()
                ->label(Status::Disabled->getLabel())
                ->badge(fn () => $this->getCount(Status::Disabled))
                ->modifyQueryUsing(fn ($query) => $query->where('status', Status::Disabled)),
        ];
    }

    protected function getCount(?Status $status = null): int
    {
        $query = Utils::getUserModel()::query();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->count();
    }
}
