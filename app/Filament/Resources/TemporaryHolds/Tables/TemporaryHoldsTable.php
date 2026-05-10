<?php

namespace App\Filament\Resources\TemporaryHolds\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TemporaryHoldsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('timeSlot.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reservation_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('reservation_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('guest_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('session_key')
                    ->searchable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
