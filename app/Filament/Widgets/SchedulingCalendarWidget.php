<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Reservation;
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

    protected int | string | array $columnSpan = 'full';

    public int $weekOffset = 0;

    protected function getViewData(): array
    {
        $builder = app(WeekCalendarBuilder::class);
        $start = WeekCalendarBuilder::weekStartSaturday()->addWeeks($this->weekOffset);

        return [
            'calendar' => $builder->build($start),
        ];
    }

    public function previousWeek(): void
    {
        $this->weekOffset--;
    }

    public function nextWeek(): void
    {
        $this->weekOffset++;
    }

    public function goToday(): void
    {
        $this->weekOffset = 0;
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

        Reservation::query()
            ->whereDate('reservation_date', $d)
            ->where('status', '!=', 'cancelled')
            ->whereTime('reservation_time', $time)
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => __('panel.dashboard.calendar.cancel_reason_reopened'),
            ]);

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
            ->title(__('panel.dashboard.calendar.copy_soon'))
            ->body(__('panel.dashboard.calendar.copy_soon_body'))
            ->info()
            ->send();
    }
}
