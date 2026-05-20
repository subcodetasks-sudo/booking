<?php

namespace App\Support;

use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class BookingConfig
{
    /**
     * Exactly one hourly reservation slot (one booking per clock hour).
     * Total volume per calendar day is limited by {@see maxReservationsPerDay()}.
     */
    public const BOOKINGS_PER_HOURLY_SLOT = 1;

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
        $startAt = (string) SiteSetting::getValue('booking_start_time', '12:00');
        $endAt = (string) SiteSetting::getValue('booking_end_time', '23:00');

        if (! preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $startAt)) {
            $startAt = '12:00';
        }
        if (! preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $endAt)) {
            $endAt = '23:00';
        }

        if ($startAt >= $endAt) {
            $startAt = '12:00';
            $endAt = '23:00';
        }

        $day = Carbon::today()->toDateString();
        $cursor = Carbon::parse($day.' '.$startAt);
        $close = Carbon::parse($day.' '.$endAt);

        $opts = [];
        while ($cursor->lt($close)) {
            $h = (int) $cursor->format('H');
            $opts[$h] = $cursor->format('H:i');
            $cursor->addHour();
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
