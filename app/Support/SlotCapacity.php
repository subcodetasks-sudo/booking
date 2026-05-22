<?php

namespace App\Support;

use App\Models\SiteSetting;
use App\Models\TimeSlot;
use Illuminate\Support\Collection;

final class SlotCapacity
{
    public static function defaultPerHour(): int
    {
        $value = SiteSetting::getValue('booking_tables_per_hour', '1');
        $n = (int) $value;

        return max(1, min(99, $n > 0 ? $n : 1));
    }

    /**
     * Tables available for a given calendar date and hour (00–23).
     *
     * @param  Collection<int, TimeSlot>|iterable<int, TimeSlot>  $daySlots
     */
    public static function forHour(string $date, int $hour, iterable $daySlots = []): int
    {
        $slot = self::findSlotForHour($daySlots, $hour);

        if ($slot?->is_closed_manually) {
            return 0;
        }

        if ($slot !== null && (int) $slot->capacity > 0) {
            return max(1, min(99, (int) $slot->capacity));
        }

        return self::defaultPerHour();
    }

    /**
     * @param  Collection<int, TimeSlot>|iterable<int, TimeSlot>  $daySlots
     */
    public static function findSlotForHour(iterable $daySlots, int $hour): ?TimeSlot
    {
        $hourStart = $hour * 60;
        $hourEnd = ($hour + 1) * 60;

        foreach ($daySlots as $slot) {
            if (! $slot instanceof TimeSlot) {
                continue;
            }

            $start = self::timeToMinutes((string) $slot->start_time);
            $end = self::timeToMinutes((string) $slot->end_time);

            if ($hourStart >= $start && $hourEnd <= $end && $end > $start) {
                return $slot;
            }
        }

        return null;
    }

    public static function hourFromTimeLabel(string $time): int
    {
        $parts = explode(':', substr($time, 0, 5));

        return max(0, min(23, (int) ($parts[0] ?? 0)));
    }

    private static function timeToMinutes(string $time): int
    {
        $parts = explode(':', substr($time, 0, 8));

        return ((int) ($parts[0] ?? 0)) * 60 + ((int) ($parts[1] ?? 0));
    }
}
