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
            'get_articles_by_category',
            'get_articles_by_doctor',
            'view_doctors',
            'view_patients',
            'view_sections',
            ///////////////////////////ADMIN////////////////////////////////////////////
            'create_doctors',
            'update_doctors',
            'delete_doctors',
            'view_doctors',
            'get_areas',
            'view_doctors_by_section',
            'get_articles_by_category',
            'get_articles_by_doctor',
            'view_patients',
            'view_sections',
//////////DOCTOR//////
            'update_doctor_profile',
            'view_doctor_profile',
            'get_medical_record',
            'get_medical_notes',
            'complete_appointment',
            'create_article',
            'update_article',
            'delete_article',
            'get_articles_by_category',
            'get_articles_by_doctor',
            /////////////////////patient////////////
            'create_appointment_by_patient',
            'availableSlots',
            'update_patient_profile',
            'get_articles_by_category',
            'get_articles_by_doctor',
            'patient_appointments',

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
