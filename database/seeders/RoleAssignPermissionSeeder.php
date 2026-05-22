<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleAssignPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo([
                'create_admins',
                'update_admin',
                'view_admins',
                'delete_admin',
                'manage_taxes',
                'manage_bonuses',
                'manage_salaries',
                'set_minimum_wage',
                'set_doctor_commission',
            ]);
        }
        $superAdmin = User::firstOrCreate(
            ['email' => 'super@admin.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'phone' => '0000000000',
                'password' => bcrypt('password123'),
                'gender' => 'male',
                'birth_date' => '1990-01-01',
                'status' => 'active'
            ]
        );
        $superAdmin->assignRole('super_admin');
    }
}
