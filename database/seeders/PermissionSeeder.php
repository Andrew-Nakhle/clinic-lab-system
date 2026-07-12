<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ////////////////////////////////////SUPER_ADMIN/////////////////////////////////////////
            'create_admins',
            'update_admin',
            'delete_admin',
            'view_admins',
            'manage_taxes',
            'manage_bonuses',
            'manage_salaries',
            'set_minimum_wage',
            'set_doctor_commission',
            ///////////////////////////ADMIN////////////////////////////////////////////
            'create_doctors',
            'update_doctors',
            'delete_doctors',
            'view_doctors',
//////////DOCTOR//////
            'update_doctor_profile',
            'view_doctor_profile',
            /////////////////////patient////////////
            'create_appointment_by_patient',
            'availableSlots',

            ///////////////////Secretaty/////////////
            'search_patient',
            'create_appointment_by_secretary',//its mean make an appointment for a patient

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }



    }
}
