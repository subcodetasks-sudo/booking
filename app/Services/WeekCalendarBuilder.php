<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ScheduleDayClosure;
use App\Models\SiteSetting;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class WeekCalendarBuilder
{
    /**
     * Week columns: Saturday → Friday.
     *
     * @return array{week_start: Carbon, week_end: Carbon, range_label: string, hours: array<int>, days: array<int, array<string, mixed>>}
     */
    public function build(Carbon $weekStartSaturday): array
    {
        $weekStart = $weekStartSaturday->copy()->startOfDay();
        $weekEnd = $weekStart->copy()->addDays(6)->startOfDay();

        [$bookingStartMin, $bookingEndMin] = $this->bookingWindowMinutes();
        $bookingIsActive = (bool) SiteSetting::getValue('booking_is_active', true);

        $closureDates = ScheduleDayClosure::query()
            ->whereBetween('closure_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->map(fn (ScheduleDayClosure $c) => Carbon::parse($c->closure_date)->toDateString());

        $reservations = Reservation::query()
            ->whereBetween('reservation_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->where('status', '!=', 'cancelled')
            ->get(['id', 'reservation_date', 'reservation_time', 'customer_name']);

        /** @var Collection<string, Reservation> $reservationByDateHour */
        $reservationByDateHour = $reservations->keyBy(function (Reservation $r): string {
            $hour = (int) substr((string) $r->reservation_time, 0, 2);

            return Carbon::parse($r->reservation_date)->toDateString() . '_' . $hour;
        });

        $slotsByDate = TimeSlot::query()
            ->whereBetween('slot_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn (TimeSlot $s) => Carbon::parse($s->slot_date)->toDateString());

        $hours = range(
            (int) floor($bookingStartMin / 60),
            max((int) floor(($bookingEndMin - 1) / 60), (int) floor($bookingStartMin / 60)),
        );

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $dateStr = $day->toDateString();
            $isHoliday = $closureDates->contains($dateStr);

            $daySlots = $slotsByDate->get($dateStr, collect());

            $cells = [];
            foreach ($hours as $hour) {
                $cells[] = $this->makeCell(
                    $dateStr,
                    $hour,
                    $isHoliday,
                    $bookingStartMin,
                    $bookingEndMin,
                    $bookingIsActive,
                    $reservationByDateHour->get($dateStr . '_' . $hour),
                    $daySlots,
                );
            }

            $locale = app()->getLocale();
            $days[] = [
                'date' => $dateStr,
                'header_primary' => $day->locale($locale)->translatedFormat('l'),
                'header_secondary' => $day->locale($locale)->translatedFormat('j M'),
                'is_holiday' => $isHoliday,
                'cells' => $cells,
            ];
        }

        return [
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'range_label' => $this->formatRangeLabel($weekStart, $weekEnd),
            'hours' => $hours,
            'days' => $days,
        ];
    }

    /**
     * Saturday start of week containing $reference (or $reference itself if Saturday).
     */
    public static function weekStartSaturday(?Carbon $reference = null): Carbon
    {
        $d = ($reference ?? Carbon::now())->copy()->startOfDay();
        $w = (int) $d->format('w'); // 0 = Sun … 6 = Sat
        $daysBack = ($w + 1) % 7;

        return $d->subDays($daysBack);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function bookingWindowMinutes(): array
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

        return [$this->timeToMinutes($startAt), $this->timeToMinutes($endAt)];
    }

    private function formatRangeLabel(Carbon $start, Carbon $end): string
    {
        $locale = app()->getLocale();

        if ($start->month === $end->month && $start->year === $end->year) {
            return $start->locale($locale)->translatedFormat('j') . ' – ' . $end->locale($locale)->translatedFormat('j F Y');
        }

        return $start->locale($locale)->translatedFormat('j M') . ' – ' . $end->locale($locale)->translatedFormat('j F Y');
    }

    private function makeCell(
        string $dateStr,
        int $hour,
        bool $isHoliday,
        int $bookingStartMin,
        int $bookingEndMin,
        bool $bookingIsActive,
        ?Reservation $reservation,
        Collection $daySlots,
    ): array {
        if ($isHoliday) {
            return [
                'hour' => $hour,
                'status' => 'holiday',
                'detail' => null,
                'reservation_id' => null,
                'closure_slot_id' => null,
            ];
        }

        if (! $bookingIsActive) {
            return [
                'hour' => $hour,
                'status' => 'disabled',
                'detail' => null,
                'reservation_id' => null,
                'closure_slot_id' => null,
            ];
        }

        if ($reservation) {
            return [
                'hour' => $hour,
                'status' => 'booked',
                'detail' => $reservation->customer_name,
                'reservation_id' => $reservation->id,
                'closure_slot_id' => null,
            ];
        }

        $manualClosedSlot = $this->findManualClosedSlotCovering($daySlots, $hour);

        if ($manualClosedSlot) {
            return [
                'hour' => $hour,
                'status' => 'unavailable',
                'detail' => null,
                'reservation_id' => null,
                'closure_slot_id' => $manualClosedSlot->id,
            ];
        }

        if (! $this->hourInBookingWindow($hour, $bookingStartMin, $bookingEndMin)) {
            return [
                'hour' => $hour,
                'status' => 'outside',
                'detail' => null,
                'reservation_id' => null,
                'closure_slot_id' => null,
            ];
        }

        return [
            'hour' => $hour,
            'status' => 'available',
            'detail' => null,
            'reservation_id' => null,
            'closure_slot_id' => null,
        ];
    }

    private function findManualClosedSlotCovering(Collection $daySlots, int $hour): ?TimeSlot
    {
        $hourStart = $hour * 60;
        $hourEnd = ($hour + 1) * 60;

        foreach ($daySlots as $slot) {
            if (! $slot->is_closed_manually) {
                continue;
            }
            $start = $this->timeToMinutes((string) $slot->start_time);
            $end = $this->timeToMinutes((string) $slot->end_time);
            if ($hourStart >= $start && $hourEnd <= $end && $end > $start) {
                return $slot;
            }
        }

        return null;
    }

    private function hourInBookingWindow(int $hour, int $bookingStartMin, int $bookingEndMin): bool
    {
        $slotStart = $hour * 60;
        $slotEnd = ($hour + 1) * 60;

        return $slotStart >= $bookingStartMin && $slotEnd <= $bookingEndMin;
    }

    private function timeToMinutes(string $time): int
    {
        $time = substr($time, 0, 8);
        $parts = explode(':', $time);

        return ((int) ($parts[0] ?? 0)) * 60 + ((int) ($parts[1] ?? 0));
    }
}
