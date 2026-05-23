<?php

namespace App\Support;

use App\Models\Reservation;
use App\Models\SiteSetting;
use App\Models\TimeSlot;
use Illuminate\Support\Collection;

final class SlotCapacity
{
    private const HOUR_CAPACITIES_KEY = 'booking_hour_capacities';

    public static function defaultPerHour(): int
    {
        $value = SiteSetting::getValue('booking_tables_per_hour', '1');
        $n = (int) $value;

        return max(1, min(99, $n > 0 ? $n : 1));
    }

    /**
     * Per-hour template (same count every calendar day until changed in the dashboard).
     *
     * @return array<int, int>
     */
    public static function hourTemplateCapacities(): array
    {
        $raw = SiteSetting::getValue(self::HOUR_CAPACITIES_KEY, '{}');
        $decoded = json_decode((string) $raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        $out = [];
        foreach ($decoded as $hour => $capacity) {
            $h = max(0, min(23, (int) $hour));
            $c = (int) $capacity;
            if ($c > 0) {
                $out[$h] = max(1, min(99, $c));
            }
        }

        return $out;
    }

    public static function hourTemplateCapacity(int $hour): ?int
    {
        $hour = max(0, min(23, $hour));

        return self::hourTemplateCapacities()[$hour] ?? null;
    }

    public static function setHourTemplateCapacity(int $hour, int $capacity): void
    {
        $hour = max(0, min(23, $hour));
        $capacity = max(1, min(99, $capacity));

        $caps = self::hourTemplateCapacities();
        $caps[$hour] = $capacity;

        SiteSetting::setValue(self::HOUR_CAPACITIES_KEY, json_encode($caps, JSON_THROW_ON_ERROR));
    }

    /**
     * Active reservations for one calendar date and booking hour (:00 slot).
     */
    public static function reservedCountForHour(string $date, int $hour): int
    {
        $hour = max(0, min(23, $hour));

        return Reservation::query()
            ->whereDate('reservation_date', $date)
            ->where('status', '!=', 'cancelled')
            ->whereTime('reservation_time', '=', sprintf('%02d:00:00', $hour))
            ->count();
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

        $template = self::hourTemplateCapacity($hour);
        if ($template !== null) {
            return $template;
        }

        return self::defaultPerHour();
    }

    public static function spotsRemaining(string $date, int $hour, iterable $daySlots = []): int
    {
        $capacity = self::forHour($date, $hour, $daySlots);
        if ($capacity < 1) {
            return 0;
        }

        return max(0, $capacity - self::reservedCountForHour($date, $hour));
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
