<?php

namespace Database\Seeders;

use App\Models\DietaryOption;
use Illuminate\Database\Seeder;

class DietaryOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            ['key' => 'general', 'name_ar' => 'حساسية', 'name_en' => 'Allergy'],
            ['key' => 'allium', 'name_ar' => 'البصل والثوم', 'name_en' => 'Allium'],
            ['key' => 'celery', 'name_ar' => 'الكرفس', 'name_en' => 'Celery'],
            ['key' => 'crustacean', 'name_ar' => 'القشريات', 'name_en' => 'Crustacean'],
            ['key' => 'dairy_free', 'name_ar' => 'خالي من الألبان', 'name_en' => 'Dairy-Free'],
            ['key' => 'diabetic', 'name_ar' => 'سكري', 'name_en' => 'Diabetic'],
            ['key' => 'eggs', 'name_ar' => 'البيض', 'name_en' => 'Eggs'],
            ['key' => 'fish', 'name_ar' => 'السمك', 'name_en' => 'Fish'],
            ['key' => 'garlic', 'name_ar' => 'الثوم', 'name_en' => 'Garlic'],
            ['key' => 'gluten', 'name_ar' => 'خالي من الغلوتين', 'name_en' => 'Gluten-Free'],
            ['key' => 'halal', 'name_ar' => 'حلال', 'name_en' => 'Halal'],
            ['key' => 'hazelnut', 'name_ar' => 'البندق', 'name_en' => 'Hazelnut'],
            ['key' => 'kosher', 'name_ar' => 'كوشر', 'name_en' => 'Kosher'],
            ['key' => 'lactose', 'name_ar' => 'لا يتحمل اللاكتوز', 'name_en' => 'Lactose intolerant'],
            ['key' => 'lupin', 'name_ar' => 'الترمس', 'name_en' => 'Lupin'],
            ['key' => 'milk', 'name_ar' => 'الحليب', 'name_en' => 'Milk'],
            ['key' => 'mushrooms', 'name_ar' => 'الفطر', 'name_en' => 'Mushrooms'],
            ['key' => 'mustard', 'name_ar' => 'الخردل', 'name_en' => 'Mustard'],
            ['key' => 'nightshade', 'name_ar' => 'البدنجانيات', 'name_en' => 'Nightshade'],
            ['key' => 'nuts', 'name_ar' => 'المكسرات', 'name_en' => 'Nuts'],
            ['key' => 'paleo', 'name_ar' => 'باليو', 'name_en' => 'Paleo'],
            ['key' => 'peanuts', 'name_ar' => 'الفول السوداني', 'name_en' => 'Peanuts'],
            ['key' => 'pescatarian', 'name_ar' => 'نباتي + سمك', 'name_en' => 'Pescatarian'],
            ['key' => 'pork', 'name_ar' => 'لحم الخنزير', 'name_en' => 'Pork'],
            ['key' => 'poultry', 'name_ar' => 'الدواجن', 'name_en' => 'Poultry'],
            ['key' => 'pregnant', 'name_ar' => 'حامل', 'name_en' => 'Pregnant'],
            ['key' => 'red_meat', 'name_ar' => 'اللحم الأحمر', 'name_en' => 'Red meat'],
            ['key' => 'salt', 'name_ar' => 'قيود على الملح', 'name_en' => 'Salt'],
            ['key' => 'seafood', 'name_ar' => 'مأكولات بحرية', 'name_en' => 'Seafood'],
            ['key' => 'sesame', 'name_ar' => 'السمسم', 'name_en' => 'Sesame'],
            ['key' => 'shellfish', 'name_ar' => 'المحار', 'name_en' => 'Shellfish'],
            ['key' => 'shrimp', 'name_ar' => 'الجمبري', 'name_en' => 'Shrimp'],
            ['key' => 'soy', 'name_ar' => 'الصويا', 'name_en' => 'Soy'],
            ['key' => 'sulfites', 'name_ar' => 'الكبريتيت', 'name_en' => 'Sulfites'],
            ['key' => 'tomatoes', 'name_ar' => 'الطماطم', 'name_en' => 'Tomatoes'],
            ['key' => 'tree_nuts', 'name_ar' => 'مكسرات شجرية', 'name_en' => 'Tree nuts'],
            ['key' => 'vegan', 'name_ar' => 'نباتي صارم', 'name_en' => 'Vegan'],
            ['key' => 'vegetarian', 'name_ar' => 'نباتي', 'name_en' => 'Vegetarian'],
            ['key' => 'walnuts', 'name_ar' => 'الجوز', 'name_en' => 'Walnuts'],
            ['key' => 'wheat', 'name_ar' => 'القمح', 'name_en' => 'Wheat'],
        ];

        foreach ($options as $index => $option) {
            DietaryOption::updateOrCreate(
                ['key' => $option['key']],
                [
                    'name_ar' => $option['name_ar'],
                    'name_en' => $option['name_en'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
