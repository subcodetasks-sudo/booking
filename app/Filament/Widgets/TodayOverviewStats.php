<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use App\Models\TimeSlot;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayOverviewStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $today = now()->toDateString();

        $todayReservations = Reservation::query()->whereDate('reservation_date', $today);

        $totalReservations = (clone $todayReservations)->count();
        $confirmedReservations = (clone $todayReservations)->where('status', 'confirmed')->count();
        $pendingReservations = (clone $todayReservations)->where('status', 'pending')->count();
        $cancelledReservations = (clone $todayReservations)->where('status', 'cancelled')->count();
        $totalGuests = (clone $todayReservations)->sum('guest_count');

        $totalCapacity = TimeSlot::query()
            ->whereDate('slot_date', $today)
            ->sum('capacity');

        $occupiedGuests = TimeSlot::query()
            ->whereDate('slot_date', $today)
            ->sum('reserved_guests');

        $occupancyRate = $totalCapacity > 0
            ? round(($occupiedGuests / $totalCapacity) * 100)
            : 0;

        return [
            Stat::make(__('panel.dashboard.kpis.total_reservations'), number_format($totalReservations)),
            Stat::make(__('panel.dashboard.kpis.confirmed'), number_format($confirmedReservations)),
            Stat::make(__('panel.dashboard.kpis.pending'), number_format($pendingReservations)),
            Stat::make(__('panel.dashboard.kpis.cancelled'), number_format($cancelledReservations)),
            Stat::make(__('panel.dashboard.kpis.total_guests'), number_format($totalGuests)),
            Stat::make(__('panel.dashboard.kpis.occupancy_rate'), "{$occupancyRate}%"),
        ];
    }
}

