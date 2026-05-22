<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {   ////////////////////////////////////SUPER_ADMIN/////////////////////////////////////////
        $permissions = [
            'create_admins',
            'update_admin',
            'delete_admin',
            'view_admins',
            'manage_taxes',
            'manage_bonuses',
            'manage_salaries',
            'set_minimum_wage',
            'set_doctor_commission',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }
    }
}
