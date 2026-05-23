<?php

namespace App\Support;

use App\Models\SiteSetting;

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

        if ($startAt >= $endAt) {
            $startAt = self::DEFAULT_START;
            $endAt = self::DEFAULT_END;
        }

        return [$startAt, $endAt];
    }

    /**
     * @return list<string> Hourly slot labels from start (inclusive) to end (exclusive).
     */
    public static function hourlySlotLabels(string $date, ?string $start = null, ?string $end = null): array
    {
        [$startAt, $endAt] = self::resolve($start, $end);

        $cursor = \Carbon\Carbon::parse($date.' '.$startAt);
        $close = \Carbon\Carbon::parse($date.' '.$endAt);
        $slots = [];

        while ($cursor->lt($close)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addHour();
        }

        return $slots;
    }
}
