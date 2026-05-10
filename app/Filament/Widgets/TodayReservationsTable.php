<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TodayReservationsTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('panel.dashboard.today_reservations'))
            ->query($this->getTableQuery())
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reservation_code')
                    ->label(__('panel.dashboard.table.reservation_code'))
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label(__('panel.dashboard.table.customer_name'))
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->label(__('panel.dashboard.table.customer_phone'))
                    ->searchable(),
                TextColumn::make('reservation_time')
                    ->label(__('panel.dashboard.table.reservation_time'))
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('guest_count')
                    ->label(__('panel.dashboard.table.guest_count'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('panel.dashboard.table.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("panel.statuses.reservation.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('order_status')
                    ->label(__('panel.dashboard.table.order_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("panel.statuses.order.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'order_placed' => 'success',
                        'no_order' => 'gray',
                        default => 'gray',
                    }),
                IconColumn::make('has_addons')
                    ->label(__('panel.dashboard.table.has_addons'))
                    ->boolean()
                    ->state(fn (Reservation $record): bool => $record->addons_total > 0),
                TextColumn::make('total_amount')
                    ->label(__('panel.dashboard.table.total_amount'))
                    ->money('AED')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('panel.dashboard.table.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('panel.dashboard.table.status'))
                    ->options([
                        'confirmed' => __('panel.statuses.reservation.confirmed'),
                        'pending' => __('panel.statuses.reservation.pending'),
                        'cancelled' => __('panel.statuses.reservation.cancelled'),
                    ]),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return Reservation::query();
    }
}

