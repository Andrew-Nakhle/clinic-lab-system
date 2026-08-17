<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            'Cardiology',
            'Pulmonology',
            'Gastroenterology',
            'Urology',
            'Ophthalmology',
            'Neurology',
            'ENT',
            'Dermatology',
            'Orthopedics',
            'Dentistry',
            'Gynecology',

        ];

        foreach ($sections as $section) {
            Section::firstOrCreate([
                'name' => $section,
            ]);
        }
    }
}
