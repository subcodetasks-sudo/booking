<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Reservation;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    public function getTabs(): array
    {
        $today = now()->toDateString();
        $weekAnchor = now();
        $weekStart = $weekAnchor->copy()->startOfWeek()->toDateString();
        $weekEnd = $weekAnchor->copy()->endOfWeek()->toDateString();

        return [
            'all' => Tab::make(__('panel.tabs.all') ?? 'All')
                ->badge(fn () => number_format(Reservation::query()->count())),
            'day' => Tab::make(__('panel.tabs.day') ?? 'Day')
                ->badge(fn () => number_format(
                    Reservation::query()->whereDate('reservation_date', $today)->count(),
                ))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('reservation_date', $today)),
            'week' => Tab::make(__('panel.tabs.week') ?? 'Week')
                ->badge(fn () => number_format(
                    Reservation::query()->whereBetween('reservation_date', [$weekStart, $weekEnd])->count(),
                ))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('reservation_date', [
                    $weekStart,
                    $weekEnd,
                ])),
            'month' => Tab::make(__('panel.tabs.month') ?? 'Month')
                ->badge(fn () => number_format(
                    Reservation::query()
                        ->whereYear('reservation_date', now()->year)
                        ->whereMonth('reservation_date', now()->month)
                        ->count(),
                ))
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereYear('reservation_date', now()->year)
                    ->whereMonth('reservation_date', now()->month)),
            'year' => Tab::make(__('panel.tabs.year') ?? 'Year')
                ->badge(fn () => number_format(
                    Reservation::query()->whereYear('reservation_date', now()->year)->count(),
                ))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereYear('reservation_date', now()->year)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
