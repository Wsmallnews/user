<?php

namespace Wsmallnews\User\Filament\Resources\Users\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Wsmallnews\User\Enums\Status;
use Wsmallnews\User\Filament\Resources\Users\UserResource;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make()
                ->label(__('sn-user::user.user_resource.tabs.all'))
                ->badge(fn (): int => $this->getCount()),
        ];

        foreach (Status::cases() as $status) {
            $tabs[$status->value] = Tab::make()
                ->label($status->getLabel())
                ->badge(fn (): int => $this->getCount($status))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', $status));
        }

        return $tabs;
    }

    protected function getCount(?Status $status = null): int
    {
        $query = static::getResource()::getEloquentQuery();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->count();
    }
}
