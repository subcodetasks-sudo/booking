<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ReservationAddon;
use Illuminate\Database\Seeder;

class ReservationAddonsSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::query()->firstOrCreate(
            ['name_ar' => 'إضافات الحجز'],
            [
                'name_en' => 'Booking add-ons',
                'is_active' => true,
                'sort_order' => 0,
            ],
        );

        $rows = [
            ['name_ar' => 'باقة ورد', 'name_en' => 'Flower Bouquet', 'price' => 150],
            ['name_ar' => 'كيك مناسبة', 'name_en' => 'Occasion Cake', 'price' => 90],
            ['name_ar' => 'تزيين الطاولة', 'name_en' => 'Table Decoration', 'price' => 120],
        ];

        foreach ($rows as $index => $row) {
            $product = Product::query()->updateOrCreate(
                ['category_id' => $category->id, 'name_ar' => $row['name_ar']],
                [
                    'name_en' => $row['name_en'],
                    'price' => $row['price'],
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );

            ReservationAddon::query()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'name_ar' => $row['name_ar'],
                    'name_en' => $row['name_en'],
                    'price' => $row['price'],
                    'is_active' => true,
                ],
            );
        }
    }
}
