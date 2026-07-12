<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// 💡 أضفنا استدعاء الموديل لكي يتعرف عليه لارافل
use App\Models\MedicalTest;

class MedicalTestSeeder extends Seeder
{
    public function run(): void
    {
        $tests = [
            [
                'name' => 'Complete Blood Count',
                'code' => 'CBC',
                'price' => 15.00,
                'display_name' => 'صورة دم كاملة',
                'normal_range' => '4.5 - 11.0 x10^3/µL'
            ],
            [
                'name' => 'Fasting Blood Sugar',
                'code' => 'FBS',
                'price' => 10.00,
                'display_name' => 'سكر صائم',
                'normal_range' => '70 - 100 mg/dL'
            ],
            [
                'name' => 'Kidney Function Test',
                'code' => 'KFT',
                'price' => 25.00,
                'display_name' => 'وظائف كلى (Creatinine)',
                'normal_range' => '0.6 - 1.2 mg/dL'
            ],
            [
                'name' => 'Liver Function Test',
                'code' => 'LFT',
                'price' => 30.00,
                'display_name' => 'وظائف كبد (ALT)',
                'normal_range' => '7 - 56 U/L'
            ],
            [
                'name' => 'Lipid Profile',
                'code' => 'LIPID',
                'price' => 20.00,
                'display_name' => 'الكوليسترول والدهون الثلاثية',
                'normal_range' => 'Desirable < 200 mg/dL'
            ],
            [
                'name' => 'Thyroid Stimulating Hormone',
                'code' => 'TSH',
                'price' => 35.00,
                'display_name' => 'هرمون الغدة الدرقية',
                'normal_range' => '0.4 - 4.0 mIU/L'
            ],
            [
                'name' => 'Vitamin D Test',
                'code' => 'VITD',
                'price' => 50.00,
                'display_name' => 'فحص فيتامين د',
                'normal_range' => '30 - 100 ng/mL'
            ],
        ];

        foreach ($tests as $test) {
            MedicalTest::create($test);
        }
    }


}
