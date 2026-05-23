<?php

namespace App\Support;

use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class BookingConfig
{
    /**
     * Default tables per hourly slot when no per-hour override exists in {@see TimeSlot}.
     *
     * @deprecated Use {@see SlotCapacity::defaultPerHour()} or {@see SlotCapacity::forHour()}
     */
    public const BOOKINGS_PER_HOURLY_SLOT = 1;

    public static function tablesPerHour(): int
    {
        return SlotCapacity::defaultPerHour();
    }

    public static function maxReservationsPerDay(): ?int
    {
        $v = SiteSetting::getValue('booking_max_reservations_per_day', null);
        if ($v === null || $v === '') {
            return null;
        }

        $n = (int) $v;

        return $n > 0 ? min($n, 10000) : null;
    }

    /**
     * Hour => start time label (matches public booking hourly slots).
     *
     * @return array<int, string>
     */
    public static function hourOptionsForFilter(): array
    {
        [$startAt, $endAt] = BookingWindow::resolve();
        $labels = BookingWindow::hourlySlotLabels(Carbon::today()->toDateString(), $startAt, $endAt);
        $opts = [];

        foreach ($labels as $label) {
            $h = (int) substr($label, 0, 2);
            $opts[$h] = $label;
        }

        return $opts;
    }

    /**
     * Reservations that start on this booking hour (:00 slot). Matches how hourly bookings are stored.
     * Uses {@see Builder::whereTime()} because the query builder's magic `whereHour()` helper can emit invalid SQL for time columns in this stack.
     */
    public static function filterQueryByBookingHour(Builder $query, mixed $hour): Builder
    {
        if ($hour === null || $hour === '') {
            return $query;
        }

        $h = max(0, min(23, (int) $hour));

        return $query->whereTime('reservation_time', '=', sprintf('%02d:00:00', $h));
    }
}
