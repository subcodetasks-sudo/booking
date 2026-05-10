<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ReservationAddon;
use App\Models\SiteSetting;
use Database\Seeders\ReservationAddonsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_reservation_from_website_payload(): void
    {
        $response = $this->postJson(route('reservations.store'), [
            'reservation_date' => now()->toDateString(),
            'reservation_time' => '18:30',
            'guest_count' => 2,
            'customer_name' => 'Test User',
            'customer_phone' => '+971500000000',
            'customer_email' => 'test@example.com',
            'occasion' => 'عيد ميلاد',
            'allergies_notes' => 'No nuts',
            'reservation_notes' => 'Window seat',
            'addons' => [
                ['name' => 'باقة ورد', 'price' => 150, 'quantity' => 1],
                ['name' => 'كيك مناسبة', 'price' => 90, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('ok', true);

        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseHas('reservations', [
            'customer_name' => 'Test User',
            'guest_count' => 2,
            'status' => 'pending',
        ]);

        $this->assertDatabaseCount('reservation_addons', 2);
        $this->assertDatabaseCount('reservation_reservation_addon', 2);
    }

    public function test_it_rejects_past_reservation_date(): void
    {
        $response = $this->postJson(route('reservations.store'), [
            'reservation_date' => now()->subDay()->toDateString(),
            'reservation_time' => '18:30',
            'guest_count' => 2,
            'customer_name' => 'Test User',
            'customer_phone' => '+971500000000',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reservation_date']);
    }

    public function test_it_respects_max_guest_count_from_site_settings(): void
    {
        SiteSetting::setValue('max_guest_count', '4');

        $response = $this->postJson(route('reservations.store'), [
            'reservation_date' => now()->toDateString(),
            'reservation_time' => '18:30',
            'guest_count' => 5,
            'customer_name' => 'Test User',
            'customer_phone' => '+971500000000',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['guest_count']);
    }

    public function test_it_accepts_leading_zero_local_mobile(): void
    {
        $response = $this->postJson(route('reservations.store'), [
            'reservation_date' => now()->toDateString(),
            'reservation_time' => '18:30',
            'guest_count' => 2,
            'customer_name' => 'Test User',
            'customer_phone' => '0545454545',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('reservations', [
            'customer_phone' => '0545454545',
        ]);
    }

    public function test_it_normalizes_arabic_indic_digits_in_phone(): void
    {
        $response = $this->postJson(route('reservations.store'), [
            'reservation_date' => now()->toDateString(),
            'reservation_time' => '18:30',
            'guest_count' => 2,
            'customer_name' => 'Test User',
            'customer_phone' => '٠٥٤٥٤٥٤٥٤٥',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('reservations', [
            'customer_phone' => '0545454545',
        ]);
    }

    public function test_it_links_addons_by_database_id(): void
    {
        $this->seed(ReservationAddonsSeeder::class);
        $product = Product::query()->where('name_ar', 'باقة ورد')->firstOrFail();
        $addon = ReservationAddon::query()->where('product_id', $product->id)->firstOrFail();

        $response = $this->postJson(route('reservations.store'), [
            'reservation_date' => now()->toDateString(),
            'reservation_time' => '18:30',
            'guest_count' => 2,
            'customer_name' => 'Test User',
            'customer_phone' => '+971500000000',
            'addons' => [
                ['addon_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('reservation_reservation_addon', [
            'reservation_addon_id' => $addon->id,
            'quantity' => 2,
        ]);
    }
}

