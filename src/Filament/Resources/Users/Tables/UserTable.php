<?php

namespace Wsmallnews\User\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Wsmallnews\Support\Filament\Actions\ActionComponents;
use Wsmallnews\User\Enums\Status;

class UserTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                static::IDColumn(),
                static::usernameColumn(),
                static::nameColumn(),
                static::emailColumn(),
                static::mobileColumn(),
                static::statusColumn(),
                static::createdAtColumn(),
                static::updatedAtColumn(),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder(__('sn-user::user.user_resource.table.search_placeholder'))
            ->filters([
                static::statusFilter(),
            ])
            ->recordActions([
                ...ActionComponents::recordActions([
                    static::toggleStatusAction(),
                ]),
            ])
            ->toolbarActions([
                ...ActionComponents::toolbarActions([
                    static::bulkEnableAction(),
                    static::bulkDisableAction(),
                ]),
            ]);
    }

    // ========================= Columns =========================

    protected static function IDColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('id')
            ->label('ID')
            ->searchable()
            ->sortable()
            ->alignCenter()
            ->toggleable();
    }

    protected static function usernameColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('username')
            ->label(__('sn-user::user.user_resource.table.username'))
            ->searchable()
            ->sortable()
            ->toggleable();
    }

    protected static function nameColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('name')
            ->label(__('sn-user::user.user_resource.table.name'))
            ->searchable()
            ->sortable()
            ->toggleable();
    }

    protected static function emailColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('email')
            ->label(__('sn-user::user.user_resource.table.email'))
            ->searchable()
            ->sortable()
            ->toggleable();
    }

    protected static function mobileColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('mobile')
            ->label(__('sn-user::user.user_resource.table.mobile'))
            ->searchable()
            ->sortable()
            ->toggleable();
    }

    protected static function statusColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('status')
            ->label(__('sn-user::user.user_resource.table.status'))
            ->badge()
            ->toggleable();
    }

    protected static function createdAtColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('created_at')
            ->label(__('sn-user::user.user_resource.table.created_at'))
            ->sortable()
            ->toggleable();
    }

    protected static function updatedAtColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('updated_at')
            ->label(__('sn-user::user.user_resource.table.updated_at'))
            ->sortable()
            ->toggleable();
    }

    // ========================= Filters =========================

    protected static function statusFilter(): Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('status')
            ->label(__('sn-user::user.user_resource.filter.status'))
            ->options(Status::class);
    }

    // ========================= Actions =========================

    protected static function toggleStatusAction(): Action
    {
        return ActionComponents::toggleAction(Status::class, 'status');
    }

    // ========================= Bulk Actions =========================

    protected static function bulkEnableAction(): BulkAction
    {
        return ActionComponents::bulkAction(
            name: 'bulk_enable',
            process: function (BulkAction $action, Model $record): void {
                if ($record->status === Status::Normal) {
                    $action->reportBulkProcessingFailure();

                    return;
                }

                $record->update(['status' => Status::Normal]);
            }
        )
            ->label(__('sn-user::user.user_resource.action.bulk_enable'))
            ->icon(Status::Normal->getIcon())
            ->color(Status::Normal->getColor())
            ->modalHeading(__('sn-user::user.user_resource.action.bulk_enable'))
            ->modalDescription(__('sn-user::user.user_resource.action.bulk_enable_description'));
    }

    protected static function bulkDisableAction(): BulkAction
    {
        return ActionComponents::bulkAction(
            name: 'bulk_disable',
            process: function (BulkAction $action, Model $record): void {
                if ($record->status === Status::Disabled) {
                    $action->reportBulkProcessingFailure();

                    return;
                }

                $record->update(['status' => Status::Disabled]);
            }
        )
            ->label(__('sn-user::user.user_resource.action.bulk_disable'))
            ->icon(Status::Disabled->getIcon())
            ->color(Status::Disabled->getColor())
            ->modalHeading(__('sn-user::user.user_resource.action.bulk_disable'))
            ->modalDescription(__('sn-user::user.user_resource.action.bulk_disable_description'));
    }
}
