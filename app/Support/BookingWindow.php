<?php

namespace App\Support;

use App\Models\SiteSetting;
use Carbon\Carbon;

final class BookingWindow
{
    public const DEFAULT_START = '12:00';

    public const DEFAULT_END = '23:00';

    /**
     * Normalize user/panel input to HH:MM (24h), or null when invalid.
     */
    public static function normalize(mixed $time): ?string
    {
        if ($time === null) {
            return null;
        }

        $time = trim((string) $time);
        if ($time === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];

            if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                return sprintf('%02d:%02d', $hour, $minute);
            }

            return null;
        }

        if (preg_match('/^\d{1,2}$/', $time)) {
            $hour = (int) $time;

            if ($hour >= 0 && $hour <= 23) {
                return sprintf('%02d:00', $hour);
            }
        }

        return null;
    }

    /**
     * Resolved booking window from settings (or explicit overrides).
     *
     * When end is earlier than start (e.g. 16:00 → 01:00), the window crosses midnight.
     *
     * @return array{0: string, 1: string}
     */
    public static function resolve(?string $start = null, ?string $end = null): array
    {
        $startAt = self::normalize($start ?? SiteSetting::getValue('booking_start_time', self::DEFAULT_START));
        $endAt = self::normalize($end ?? SiteSetting::getValue('booking_end_time', self::DEFAULT_END));

        if ($startAt === null) {
            $startAt = self::DEFAULT_START;
        }
        if ($endAt === null) {
            $endAt = self::DEFAULT_END;
        }

        if ($startAt === $endAt) {
            $startAt = self::DEFAULT_START;
            $endAt = self::DEFAULT_END;
        }

        return [$startAt, $endAt];
    }

    public static function crossesMidnight(?string $start = null, ?string $end = null): bool
    {
        [$startAt, $endAt] = self::resolve($start, $end);

        return $startAt >= $endAt;
    }

    /**
     * @return list<int> Hour numbers (0–23) inside the booking window.
     */
    public static function hoursInWindow(?string $start = null, ?string $end = null): array
    {
        [$startAt, $endAt] = self::resolve($start, $end);

        $hours = array_values(array_filter(
            range(0, 23),
            fn (int $hour): bool => self::hourInWindow($hour, $startAt, $endAt),
        ));

        if ($startAt >= $endAt) {
            $startHour = (int) substr($startAt, 0, 2);
            usort(
                $hours,
                fn (int $a, int $b): int => self::overnightHourRank($a, $startHour) <=> self::overnightHourRank($b, $startHour),
            );
        }

        return $hours;
    }

    public static function hourInWindow(int $hour, ?string $start = null, ?string $end = null): bool
    {
        [$startAt, $endAt] = self::resolve($start, $end);

        return self::hourInResolvedWindow(max(0, min(23, $hour)), $startAt, $endAt);
    }

    /**
     * @return list<string> Hourly slot labels from start (inclusive) to end (exclusive).
     */
    public static function hourlySlotLabels(string $date, ?string $start = null, ?string $end = null): array
    {
        [$startAt, $endAt] = self::resolve($start, $end);

        $cursor = Carbon::parse($date.' '.$startAt);
        $close = Carbon::parse($date.' '.$endAt);
        if ($startAt >= $endAt) {
            $close->addDay();
        }

        $slots = [];
        while ($cursor->lt($close)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addHour();
        }

        return $slots;
    }

    private static function hourInResolvedWindow(int $hour, string $startAt, string $endAt): bool
    {
        $slotStart = $hour * 60;
        $startMin = self::timeToMinutes($startAt);
        $endMin = self::timeToMinutes($endAt);

        if ($startAt >= $endAt) {
            return $slotStart >= $startMin || $slotStart < $endMin;
        }

        return $slotStart >= $startMin && $slotStart < $endMin;
    }

    private static function overnightHourRank(int $hour, int $startHour): int
    {
        return $hour >= $startHour ? $hour : $hour + 24;
    }

    private static function timeToMinutes(string $time): int
    {
        $parts = explode(':', substr($time, 0, 5));

        return ((int) ($parts[0] ?? 0)) * 60 + ((int) ($parts[1] ?? 0));
    }
}
