<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Area::insert([
            ['name' => 'Mazzeh'],
            ['name' => 'Maliki'],
            ['name' => 'Abu Rummaneh'],
            ['name' => 'Muhajireen'],
            ['name' => 'Rukn Al-Din'],
            ['name' => 'Barzeh'],
            ['name' => 'Qaboun'],
            ['name' => 'Jobar'],
            ['name' => 'Bab Touma'],
            ['name' => 'Bab Sharqi'],
            ['name' => 'Sarouja'],
            ['name' => 'Al Midan'],
            ['name' => 'Al Shaghour'],
            ['name' => 'Al Qanawat'],
            ['name' => 'Al Tijarah'],
            ['name' => 'Dummar'],
            ['name' => 'Mashrou Dummar'],
            ['name' => 'Qudsaya'],
            ['name' => 'Jaramana'],
            ['name' => 'Sahnaya'],
            ['name' => 'Ashrafiyat Sahnaya'],
            ['name' => 'Yarmouk'],
            ['name' => 'Zahira'],
            ['name' => 'Nahr Aisha'],
            ['name' => 'Kafr Sousa'],
            ['name' => 'Harasta'],
            ['name' => 'Douma'],
            ['name' => 'Arbin'],
            ['name' => 'Zamalka'],
        ]);
    }
}
