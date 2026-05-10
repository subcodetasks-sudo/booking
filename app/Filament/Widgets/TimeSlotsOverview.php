<?php

namespace App\Filament\Widgets;

use App\Models\TimeSlot;
use Filament\Widgets\Widget;

class TimeSlotsOverview extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.widgets.time-slots-overview';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $today = now()->toDateString();

        $slots = TimeSlot::query()
            ->whereDate('slot_date', $today)
            ->orderBy('start_time')
            ->get()
            ->map(function (TimeSlot $slot): array {
                $available = max(0, $slot->capacity - $slot->reserved_guests - $slot->held_guests);
                $isUnavailable = $slot->is_closed_manually || ($available <= 0);

                return [
                    'time_range' => substr((string) $slot->start_time, 0, 5) . ' - ' . substr((string) $slot->end_time, 0, 5),
                    'capacity' => $slot->capacity,
                    'reserved' => $slot->reserved_guests,
                    'held' => $slot->held_guests,
                    'available' => $available,
                    'is_unavailable' => $isUnavailable,
                    'is_closed_manually' => $slot->is_closed_manually,
                ];
            })
            ->all();

        return [
            'slots' => $slots,
        ];
    }
}

