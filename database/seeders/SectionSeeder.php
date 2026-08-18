<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            'Cardiology' => 10,
            'Pulmonology' => 8,
            'Gastroenterology' => 8,
            'Urology' => 8,
            'Ophthalmology' => 7,
            'Neurology' => 10,
            'ENT' => 6,
            'Dermatology' => 7,
            'Orthopedics' => 10,
            'Dentistry' => 7,
            'Gynecology' => 8,
        ];

        foreach ($sections as $section => $price) {
            Section::updateOrCreate(
                ['name' => $section],
                ['base_price' => $price]
            );
        }
    }
}
