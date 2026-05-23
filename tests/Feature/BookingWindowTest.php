<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Support\BookingWindow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingWindowTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalize_accepts_single_digit_hour_and_bare_hour(): void
    {
        $this->assertSame('04:00', BookingWindow::normalize('4:00'));
        $this->assertSame('04:00', BookingWindow::normalize('4'));
        $this->assertSame('12:00', BookingWindow::normalize('12:00'));
    }

    public function test_resolve_uses_saved_site_settings(): void
    {
        SiteSetting::setValue('booking_start_time', '04:00');
        SiteSetting::setValue('booking_end_time', '12:00');

        $this->assertSame(['04:00', '12:00'], BookingWindow::resolve());
    }

    public function test_booking_window_endpoint_returns_saved_settings(): void
    {
        SiteSetting::setValue('booking_start_time', '04:00');
        SiteSetting::setValue('booking_end_time', '12:00');
        SiteSetting::setValue('booking_is_active', '1');

        $this->getJson('/booking-window')
            ->assertOk()
            ->assertJson([
                'booking_start' => '04:00',
                'booking_end' => '12:00',
                'booking_active' => true,
            ]);
    }

    public function test_availability_returns_slots_inside_booking_window(): void
    {
        SiteSetting::setValue('booking_start_time', '04:00');
        SiteSetting::setValue('booking_end_time', '12:00');
        SiteSetting::setValue('booking_is_active', '1');

        $date = now()->addDay()->toDateString();

        $response = $this->getJson('/availability?date='.$date);
        $response->assertOk();

        $times = collect($response->json('slots'))->pluck('time')->all();

        $this->assertSame(
            ['04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00'],
            $times,
        );
        $this->assertSame('04:00', $response->json('booking_start'));
        $this->assertSame('12:00', $response->json('booking_end'));
    }
}
