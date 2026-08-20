<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MedicalTest;

class MedicalTestSeeder extends Seeder
{
    public function run(): void
    {
        $tests = [
            [
                'name'         => 'CBC',
                'code'         => 'CBC',
                'price'        => 12.00,
                'display_name' => 'صورة دم كاملة',
                'normal_range' => '4.5 - 11.0 x10^3/µL'
            ],
            [
                'name'         => 'Blood Sugar',
                'code'         => 'FBS',
                'price'        => 12.00,
                'display_name' => 'سكر صائم',
                'normal_range' => '70 - 100 mg/dL'
            ],
            [
                'name'         => 'Vitamin D',
                'code'         => 'VITD',
                'price'        => 12.00,
                'display_name' => 'فحص فيتامين د',
                'normal_range' => '30 - 100 ng/mL'
            ],
            [
                'name'         => 'Cholesterol',
                'code'         => 'LIPID',
                'price'        => 12.00,
                'display_name' => 'الكوليسترول والدهون',
                'normal_range' => 'Desirable < 200 mg/dL'
            ],
            [
                'name'         => 'Iron Test',
                'code'         => 'FE',
                'price'        => 12.00,
                'display_name' => 'فحص الحديد',
                'normal_range' => '60 - 170 mcg/dL'
            ],
            [
                'name'         => 'Thyroid Test',
                'code'         => 'TSH',
                'price'        => 12.00,
                'display_name' => 'هرمون الغدة الدرقية',
                'normal_range' => '0.4 - 4.0 mIU/L'
            ],
        ];

        foreach ($tests as $test) {
            MedicalTest::updateOrCreate(
                ['code' => $test['code']],
                [
                    'name'         => $test['name'],
                    'price'        => $test['price'],
                    'display_name' => $test['display_name'],
                    'normal_range' => $test['normal_range'],
                ]
            );
        }
    }
}
