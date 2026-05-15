<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {



        $admin = Role::firstOrCreate(['name' => 'admin']);
        $patient  = Role::firstOrCreate(['name' => 'patient']);
        $secretary=Role::firstOrCreate(['name' => 'secretary']);
        $super_admin=Role::firstOrCreate(['name' => 'super admin']);
        $doctor=Role::firstOrCreate(['name' => 'doctor']);
        $lab_technician=Role::firstOrCreate(['name' => 'lab_technician']);



        $user = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'phone' => '0999999999',
                'password' => Hash::make('password123'),
                'gender' => 'male',
                'birth_date' => '2000-01-01',
            ]
        );

        $user->assignRole('super admin');





//        Permission::firstOrCreate(['name'=>'register admin']);
        $permissions = [
            'register admin',

        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission
            ]);
        }
        $super_admin->givePermissionTo([
            'register admin',]);
    }
}
