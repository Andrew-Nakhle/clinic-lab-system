<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'secretary', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'laboratory', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'api']);

    }
}
