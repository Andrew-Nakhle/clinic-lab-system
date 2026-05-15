<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
        $super_admin=Role::firstOrCreate(['name' => 'super_admin']);
        $doctor=Role::firstOrCreate(['name' => 'doctor']);
        $lab_technician=Role::firstOrCreate(['name' => 'lab_technician']);


    }
}
