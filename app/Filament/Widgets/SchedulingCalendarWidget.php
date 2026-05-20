<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\ScheduleDayClosure;
use App\Models\TimeSlot;
use App\Services\WeekCalendarBuilder;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class SchedulingCalendarWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = -60;

    protected string $view = 'filament.widgets.scheduling-calendar';

    protected int|string|array $columnSpan = 'full';

    public int $weekOffset = 0;

    public string $statusFilter = 'available';

    public ?string $selectedDay = null;

    public function mount(): void
    {
        if (! in_array($this->statusFilter, ['available', 'booked', 'unavailable'], true)) {
            $this->statusFilter = 'available';
        }

        $this->ensureSelectedDay();
    }

    protected function getViewData(): array
    {
        $calendar = $this->getCalendar();
        $this->ensureSelectedDay($calendar);

        return [
            'calendar' => $calendar,
            'statistics' => $calendar['statistics'],
            'week_days' => $this->buildWeekDays($calendar),
            'active_day' => $this->buildActiveDay($calendar),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getCalendar(): array
    {
        $builder = app(WeekCalendarBuilder::class);
        $start = WeekCalendarBuilder::weekStartSaturday()->addWeeks($this->weekOffset);

        return $builder->build($start);
    }

    /**
     * @param  array<string, mixed>|null  $calendar
     */
    protected function ensureSelectedDay(?array $calendar = null): void
    {
        $calendar ??= $this->getCalendar();
        $dates = array_column($calendar['days'], 'date');

        if ($this->selectedDay !== null && in_array($this->selectedDay, $dates, true)) {
            return;
        }

        $today = Carbon::today()->toDateString();

        if (in_array($today, $dates, true)) {
            $this->selectedDay = $today;

            return;
        }

        $this->selectedDay = $dates[0] ?? null;
    }

    /**
     * @param  array<string, mixed>  $calendar
     * @return array<int, array<string, mixed>>
     */
    protected function buildWeekDays(array $calendar): array
    {
        $today = Carbon::today()->toDateString();
        $days = [];

        foreach ($calendar['days'] as $day) {
            $counts = $this->countDayStatuses($day['cells']);

            $days[] = [
                'date' => $day['date'],
                'day_primary' => $day['header_primary'],
                'day_secondary' => $day['header_secondary'],
                'is_holiday' => (bool) $day['is_holiday'],
                'is_today' => $day['date'] === $today,
                'counts' => $counts,
                'filter_count' => $counts[$this->statusFilter] ?? 0,
            ];
        }

        return $days;
    }

    /**
     * @param  array<string, mixed>  $calendar
     * @return array<string, mixed>|null
     */
    protected function buildActiveDay(array $calendar): ?array
    {
        if ($this->selectedDay === null) {
            return null;
        }

        foreach ($calendar['days'] as $day) {
            if ($day['date'] !== $this->selectedDay) {
                continue;
            }

            $slots = $this->buildSlotsForDay($day, $this->statusFilter);
            $counts = $this->countDayStatuses($day['cells']);

            return [
                'date' => $day['date'],
                'day_primary' => $day['header_primary'],
                'day_secondary' => $day['header_secondary'],
                'is_holiday' => (bool) $day['is_holiday'],
                'counts' => $counts,
                'slots' => $slots,
                'slot_count' => count($slots),
            ];
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $cells
     * @return array{available: int, booked: int, unavailable: int}
     */
    protected function countDayStatuses(array $cells): array
    {
        $counts = ['available' => 0, 'booked' => 0, 'unavailable' => 0];

        foreach ($cells as $cell) {
            $status = $cell['status'] ?? 'outside';
            if (isset($counts[$status])) {
                $counts[$status]++;
            }
        }

        return $counts;
    }

    /**
     * @param  array<string, mixed>  $day
     * @return array<int, array<string, mixed>>
     */
    protected function buildSlotsForDay(array $day, string $statusFilter): array
    {
        $slots = [];

        foreach ($day['cells'] as $cell) {
            $status = $cell['status'] ?? 'outside';

            if ($status !== $statusFilter) {
                continue;
            }

            $hour = (int) $cell['hour'];
            $slots[] = [
                'id' => $day['date'].'_'.$hour,
                'date' => $day['date'],
                'hour' => $hour,
                'time_label' => sprintf('%02d:00 – %02d:00', $hour, $hour + 1),
                'status' => $status,
                'detail' => $cell['detail'] ?? null,
                'reservation_id' => $cell['reservation_id'] ?? null,
                'reserved_count' => (int) ($cell['reserved_count'] ?? 0),
                'capacity' => (int) ($cell['capacity'] ?? 1),
            ];
        }

        return $slots;
    }

    public function selectDay(string $date): void
    {
        $this->selectedDay = $date;
    }

    public function updatedStatusFilter(): void
    {
        //
    }

    public function previousWeek(): void
    {
        $this->weekOffset--;
        $this->selectedDay = null;
        $this->ensureSelectedDay();
    }

    public function nextWeek(): void
    {
        $this->weekOffset++;
        $this->selectedDay = null;
        $this->ensureSelectedDay();
    }

    public function goToday(): void
    {
        $this->weekOffset = 0;
        $this->selectedDay = Carbon::today()->toDateString();
        $this->ensureSelectedDay();
    }

    public function openAvailabilitySettings(): void
    {
        $this->redirect(SiteSettings::getUrl(panel: 'admin'));
    }

    public function createReservation(): void
    {
        $this->redirect(ReservationResource::getUrl('create', panel: 'admin'));
    }

    public function toggleDayClosure(string $date): void
    {
        $d = Carbon::parse($date)->toDateString();

        $exists = ScheduleDayClosure::query()->whereDate('closure_date', $d)->exists();

        if ($exists) {
            ScheduleDayClosure::query()->whereDate('closure_date', $d)->delete();
            Notification::make()
                ->title(__('panel.dashboard.calendar.day_opened'))
                ->success()
                ->send();

            return;
        }

        ScheduleDayClosure::query()->create(['closure_date' => $d]);
        Notification::make()
            ->title(__('panel.dashboard.calendar.day_closed'))
            ->success()
            ->send();
    }

    public function markHourUnavailable(string $date, int $hour): void
    {
        $d = Carbon::parse($date)->toDateString();

        TimeSlot::query()->updateOrCreate(
            [
                'slot_date' => $d,
                'start_time' => sprintf('%02d:00:00', $hour),
            ],
            [
                'end_time' => sprintf('%02d:00:00', $hour + 1),
                'capacity' => 0,
                'reserved_guests' => 0,
                'held_guests' => 0,
                'is_closed_manually' => true,
            ]
        );

        Notification::make()
            ->title(__('panel.dashboard.calendar.hour_blocked'))
            ->success()
            ->send();
    }

    public function markHourAvailable(string $date, int $hour): void
    {
        $d = Carbon::parse($date)->toDateString();
        $time = sprintf('%02d:00:00', $hour);

        TimeSlot::query()
            ->whereDate('slot_date', $d)
            ->whereTime('start_time', $time)
            ->where('is_closed_manually', true)
            ->delete();

        Notification::make()
            ->title(__('panel.dashboard.calendar.hour_opened'))
            ->success()
            ->send();
    }

    public function placeholderCopySchedule(): void
    {
        Notification::make()
           // ->title(__('panel.dashboard.calendar.copy_soon'))
          //  ->body(__('panel.dashboard.calendar.copy_soon_body'))
            ->info()
            ->send();
    }
}
