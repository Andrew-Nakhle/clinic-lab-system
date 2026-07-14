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
            'Dermatology',
            'Pediatrics',
            'Orthopedics',
            'Neurology',
            'laboratory',
            'surgery '
        ];

        foreach ($sections as $section) {
            Section::firstOrCreate([
                'name' => $section,
            ]);
        }
    }
}
