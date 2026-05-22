<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\SiteSetting;
use App\Services\WeekCalendarBuilder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeekCalendarBookingWindowTest extends TestCase
{
    use RefreshDatabase;

    public function test_hour_outside_booking_window_is_not_marked_booked(): void
    {
        SiteSetting::setValue('booking_start_time', '12:00');
        SiteSetting::setValue('booking_end_time', '23:00');

        $date = Carbon::today()->toDateString();

        Reservation::query()->create([
            'reservation_code' => 'OUT-1',
            'reservation_date' => $date,
            'reservation_time' => '23:00',
            'guest_count' => 2,
            'status' => 'pending',
            'order_status' => 'no_order',
            'customer_name' => 'Late Guest',
            'customer_phone' => '0500000099',
        ]);

        $calendar = app(WeekCalendarBuilder::class)->build(WeekCalendarBuilder::weekStartSaturday());
        $day = collect($calendar['days'])->firstWhere('date', $date);
        $this->assertNotNull($day);

        $hour23 = collect($day['cells'])->firstWhere('hour', 23);
        $this->assertNull($hour23, '23:00 must not appear when booking ends at 23:00');

        $hour22 = collect($day['cells'])->firstWhere('hour', 22);
        $this->assertNotNull($hour22);
        $this->assertSame('available', $hour22['status']);
    }

    public function test_last_allowed_hour_can_show_booked_when_full(): void
    {
        SiteSetting::setValue('booking_start_time', '12:00');
        SiteSetting::setValue('booking_end_time', '23:00');
        SiteSetting::setValue('booking_tables_per_hour', '1');

        $date = Carbon::today()->toDateString();

        Reservation::query()->create([
            'reservation_code' => 'LAST-1',
            'reservation_date' => $date,
            'reservation_time' => '22:00',
            'guest_count' => 2,
            'status' => 'pending',
            'order_status' => 'no_order',
            'customer_name' => 'Guest',
            'customer_phone' => '0500000001',
        ]);

        $calendar = app(WeekCalendarBuilder::class)->build(WeekCalendarBuilder::weekStartSaturday());
        $day = collect($calendar['days'])->firstWhere('date', $date);
        $hour22 = collect($day['cells'])->firstWhere('hour', 22);

        $this->assertSame('booked', $hour22['status']);
        $this->assertSame(1, $hour22['reserved_count']);
    }
}
