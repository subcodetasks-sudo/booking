<?php

namespace Database\Seeders;

use App\Models\Occasion;
use Illuminate\Database\Seeder;

class OccasionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            ['name_ar' => 'حفل توديع العزوبية', 'name_en' => 'Bachelor(ette) Party'],
            ['name_ar' => 'احتفال شركة', 'name_en' => 'Business Celebration'],
            ['name_ar' => 'اجتماع عمل', 'name_en' => 'Business Meeting'],
            ['name_ar' => 'خطوبة', 'name_en' => 'Engagement'],
            ['name_ar' => 'عشاء عائلي', 'name_en' => 'Family Dinner'],
            ['name_ar' => 'عيد ميلاد (سيدات)', 'name_en' => 'Female Birthday'],
            ['name_ar' => 'تخرج', 'name_en' => 'Graduation'],
            ['name_ar' => 'شهر عسل', 'name_en' => 'Honeymoon'],
            ['name_ar' => 'عيد ميلاد (رجال)', 'name_en' => 'Male Birthday'],
            ['name_ar' => 'طلب جلسة خارجية', 'name_en' => 'Outdoor request'],
            ['name_ar' => 'عشاء رومانسي', 'name_en' => 'Romantic Dinner'],
            ['name_ar' => 'زفاف', 'name_en' => 'Wedding'],
            ['name_ar' => 'ذكرى زواج', 'name_en' => 'Wedding anniversary'],
        ];

        foreach ($options as $option) {
            Occasion::updateOrCreate(
                ['name_ar' => $option['name_ar']],
                [
                    'name_en' => $option['name_en'],
                    'is_active' => true,
                ],
            );
        }
    }
}
