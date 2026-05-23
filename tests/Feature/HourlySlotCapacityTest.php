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

    public function test_same_hour_is_available_next_day_after_filling_capacity(): void
    {
        SiteSetting::setValue('booking_tables_per_hour', '2');

        $dayOne = now()->toDateString();
        $dayTwo = now()->addDay()->toDateString();
        $time = '18:00';

        foreach (['A', 'B'] as $index => $name) {
            Reservation::query()->create([
                'reservation_code' => 'DAY1-'.$index,
                'reservation_date' => $dayOne,
                'reservation_time' => $time,
                'guest_count' => 2,
                'status' => 'pending',
                'order_status' => 'no_order',
                'customer_name' => $name,
                'customer_phone' => '050000000'.($index + 1),
            ]);
        }

        $dayOneSlot = collect($this->getJson('/availability?date='.$dayOne)->json('slots'))
            ->firstWhere('time', $time);
        $this->assertSame('booked', $dayOneSlot['status']);

        $dayTwoSlot = collect($this->getJson('/availability?date='.$dayTwo)->json('slots'))
            ->firstWhere('time', $time);
        $this->assertNotNull($dayTwoSlot);
        $this->assertSame('available', $dayTwoSlot['status']);
        $this->assertSame(2, $dayTwoSlot['capacity']);
        $this->assertSame(0, $dayTwoSlot['reserved']);
        $this->assertSame(2, $dayTwoSlot['spots_remaining']);
    }

    public function test_hour_template_capacity_applies_on_following_days(): void
    {
        SiteSetting::setValue('booking_tables_per_hour', '1');
        \App\Support\SlotCapacity::setHourTemplateCapacity(18, 4);

        $dayOne = now()->toDateString();
        $dayTwo = now()->addDay()->toDateString();

        for ($i = 0; $i < 4; $i++) {
            Reservation::query()->create([
                'reservation_code' => 'TMP-'.$i,
                'reservation_date' => $dayOne,
                'reservation_time' => '18:00',
                'guest_count' => 2,
                'status' => 'pending',
                'order_status' => 'no_order',
                'customer_name' => 'Guest '.$i,
                'customer_phone' => '05000000'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            ]);
        }

        $dayTwoSlot = collect($this->getJson('/availability?date='.$dayTwo)->json('slots'))
            ->firstWhere('time', '18:00');

        $this->assertSame('available', $dayTwoSlot['status']);
        $this->assertSame(4, $dayTwoSlot['capacity']);
        $this->assertSame(4, $dayTwoSlot['spots_remaining']);
    }
}
