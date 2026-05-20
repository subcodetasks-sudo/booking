<?php

namespace App\Filament\Support;

use App\Support\BookingConfig;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

final class ReservationTableFilters
{
    public static function reservationDate(?string $defaultDate = null): Filter
    {
        $filter = Filter::make('reservation_date')
            ->label(__('panel.dashboard.table.reservation_date'))
            ->schema([
                DatePicker::make('date')
                    ->label(__('panel.dashboard.table.reservation_date'))
                    ->native(false),
            ])
            ->query(function (Builder $query, array $data): Builder {
                $date = $data['date'] ?? null;
                if (blank($date)) {
                    return $query;
                }

                return $query->whereDate('reservation_date', $date);
            })
            ->indicateUsing(function (array $data): array {
                if (blank($data['date'] ?? null)) {
                    return [];
                }

                return [
                    Indicator::make(__('panel.dashboard.table.reservation_date').': '.$data['date']),
                ];
            });

        if ($defaultDate !== null) {
            $filter->default(['date' => $defaultDate]);
        }

        return $filter;
    }

    public static function reservationHour(): SelectFilter
    {
        return SelectFilter::make('reservation_hour')
            ->label(__('panel.dashboard.table.reservation_hour'))
            ->options(static function (): array {
                $opts = BookingConfig::hourOptionsForFilter();

                return collect($opts)
                    ->mapWithKeys(fn (string $label, int $hour): array => [(string) $hour => $label])
                    ->all();
            })
            ->query(function (Builder $query, array $data): Builder {
                return BookingConfig::filterQueryByBookingHour($query, $data['value'] ?? null);
            });
    }
}
