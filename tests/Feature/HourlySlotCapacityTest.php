<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HourlySlotCapacityTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_bookings_same_hour_until_capacity(): void
    {
        SiteSetting::setValue('booking_tables_per_hour', '2');

        $date = now()->toDateString();
        $time = '18:00';

        $payload = [
            'reservation_date' => $date,
            'reservation_time' => $time,
            'guest_count' => 2,
            'customer_name' => 'Guest',
            'customer_phone' => '0545454545',
        ];

        $this->postJson(route('reservations.store'), $payload)->assertCreated();
        $this->postJson(route('reservations.store'), $payload)->assertCreated();

        $this->postJson(route('reservations.store'), $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['reservation_time']);

        $this->assertSame(2, Reservation::query()->whereDate('reservation_date', $date)->count());
    }

    public function test_availability_marks_hour_booked_when_capacity_reached(): void
    {
        SiteSetting::setValue('booking_tables_per_hour', '2');

        $date = now()->toDateString();
        $time = '18:00';

        Reservation::query()->create([
            'reservation_code' => 'TEST-1',
            'reservation_date' => $date,
            'reservation_time' => $time,
            'guest_count' => 2,
            'status' => 'pending',
            'order_status' => 'no_order',
            'customer_name' => 'A',
            'customer_phone' => '0500000001',
        ]);

        Reservation::query()->create([
            'reservation_code' => 'TEST-2',
            'reservation_date' => $date,
            'reservation_time' => $time,
            'guest_count' => 2,
            'status' => 'pending',
            'order_status' => 'no_order',
            'customer_name' => 'B',
            'customer_phone' => '0500000002',
        ]);

        $response = $this->getJson('/availability?date='.$date);
        $response->assertOk();

        $slot = collect($response->json('slots'))->firstWhere('time', $time);
        $this->assertNotNull($slot);
        $this->assertTrue($slot['is_unavailable']);
        $this->assertSame('booked', $slot['status']);
        $this->assertSame(0, $slot['spots_remaining']);
        $this->assertSame(2, $slot['capacity']);
        $this->assertSame(2, $slot['reserved']);
    }
}
