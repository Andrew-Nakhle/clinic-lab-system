<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use App\Models\User;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAssignPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // جلب أول قسم متاح في قاعدة البيانات لتفادي أخطاء العلاقات
        $defaultSectionId = Section::first()?->id ?? null;

        /*
        |--------------------------------------------------------------------------
        | 1. إعداد صلاحيات الـ Super Admin وإنشاء حسابه
        |--------------------------------------------------------------------------
        */
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo([
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

        $superAdminUser = User::firstOrCreate(
            ['email' => 'super@admin.com'],
            [
                'first_name' => 'Super',
                'last_name'  => 'Admin',
                'phone'      => '0000000000',
                'password'   => bcrypt('abc123'),
                'gender'     => 'male',
                'birth_date' => '1990-01-01',
                'status'     => UserStatus::Active ?? 'active'
            ]
        ); // تم إضافة الفاصلة المنقوطة هنا

        $superAdminUser->assignRole('super_admin'); // تم تصحيح اسم المتغير

        // صلاحيات الأدمن والمسؤولين
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'create_doctors',
                'update_doctors',
                'view_doctors',
                'delete_doctors',
                'get_areas',
                'view_doctors_by_section'
            ]);
        }

        $doctorRole = Role::where('name', 'doctor')->first();
        if ($doctorRole) {
            $doctorRole->givePermissionTo([
                'update_doctor_profile',
                'view_doctor_profile',
                'get_medical_record',
                'get_medical_notes',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Admin 1
        |--------------------------------------------------------------------------
        */

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => '1',
            'phone' => '0999000001',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'status' => UserStatus::Active->value,
        ]);

        $admin->assignRole('admin');


        /*
        |--------------------------------------------------------------------------
        | Doctor 1
        |--------------------------------------------------------------------------
        */

        $doctorUser1 = User::create([
            'first_name' => 'Doctor',
            'last_name' => '1',
            'phone' => '0999000002',
            'email' => 'doctor1@gmail.com',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'birth_date' => '1985-05-10',
            'status' => UserStatus::Active->value,
        ]);

        $doctorUser1->assignRole('doctor');

        $doctor1 = DoctorProfile::create([
            'user_id' => $doctorUser1->id,
            'section_id' => null,
            'specialization' => 'Cardiology',
            'qualification' => 'MD',
            'experience_years' => 10,
            'bio' => 'Test Doctor 1',
            'certification' => 'Cardiology Certificate',
            'profile_image' => null,
            'consultation_fee' => 10.00,
            'home_visit_fee' => 20.00,
            'monthly_salary' => 1000.00,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Doctor 2
        |--------------------------------------------------------------------------
        */

        $doctorUser2 = User::create([
            'first_name' => 'Doctor',
            'last_name' => '2',
            'phone' => '0999000003',
            'email' => 'doctor2@gmail.com',
            'password' => Hash::make('password'),
            'gender' => 'female',
            'birth_date' => '1988-08-15',
            'status' => UserStatus::Active->value,
        ]);

        $doctorUser2->assignRole('doctor');

        $doctor2 = DoctorProfile::create([
            'user_id' => $doctorUser2->id,
            'section_id' => null,
            'specialization' => 'Dermatology',
            'qualification' => 'MD',
            'experience_years' => 7,
            'bio' => 'Test Doctor 2',
            'certification' => 'Dermatology Certificate',
            'profile_image' => null,
            'consultation_fee' => 15.00,
            'home_visit_fee' => 25.00,
            'monthly_salary' => 1200.00,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Doctor 1 Schedules
        |--------------------------------------------------------------------------
        */

        $doctor1Schedules = [
            ['day_of_week' => 'Sunday',    'start_time' => '12:00', 'end_time' => '16:00', 'schedule_type' => 'clinic'],
            ['day_of_week' => 'Monday',    'start_time' => '12:00', 'end_time' => '16:00', 'schedule_type' => 'clinic'],
            ['day_of_week' => 'Tuesday',   'start_time' => '12:00', 'end_time' => '16:00', 'schedule_type' => 'clinic'],
            ['day_of_week' => 'Wednesday', 'start_time' => '12:00', 'end_time' => '16:00', 'schedule_type' => 'clinic'],

            ['day_of_week' => 'Thursday',  'start_time' => '12:00', 'end_time' => '16:00', 'schedule_type' => 'home'],
            ['day_of_week' => 'Saturday',  'start_time' => '12:00', 'end_time' => '16:00', 'schedule_type' => 'clinic'],
        ];

        foreach ($doctor1Schedules as $schedule) {
            $doctor1->schedules()->create($schedule);
        }


        /*
        |--------------------------------------------------------------------------
        | Doctor 2 Schedules
        |--------------------------------------------------------------------------
        */

        $doctor2Schedules = [
            ['day_of_week' => 'Sunday',    'start_time' => '12:00', 'end_time' => '16:00', 'schedule_type' => 'clinic'],
            ['day_of_week' => 'Monday',    'start_time' => '12:00', 'end_time' => '16:00', 'schedule_type' => 'clinic'],
            ['day_of_week' => 'Tuesday',    'start_time' => '12:00', 'end_time' => '16:00', 'schedule_type' => 'clinic'],
            ['day_of_week' => 'Wednesday', 'start_time' => '12:00', 'end_time' => '16:00', 'schedule_type' => 'clinic'],

            ['day_of_week' => 'Thursday',  'start_time' => '12:00', 'end_time' => '16:00', 'schedule_type' => 'home'],
            ['day_of_week' => 'Saturday',  'start_time' => '12:00', 'end_time' => '16:00', 'schedule_type' => 'home'],
        ];

        foreach ($doctor2Schedules as $schedule) {
            $doctor2->schedules()->create($schedule);
        }


        /*
        |--------------------------------------------------------------------------
        | Patient 1
        |--------------------------------------------------------------------------
        */

        $patientUser1 = User::create([
            'first_name' => 'Patient',
            'last_name' => '1',
            'phone' => '0999000004',
            'email' => 'patient1@gmail.com',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'birth_date' => '2000-01-15',
            'status' => UserStatus::Active->value,
        ]);

        $patientUser1->assignRole('patient');

        PatientProfile::create([
            'user_id' => $patientUser1->id,
            'section_id' => null,
            'profile_image' => null,
            'id_card' =>  'id_cards/OmVfSdVCkIp0UPDM2AbfZ4zZ6zkwKhElvyww7hz.jpg',
            'blood_group' => 'A+',
            'tall' => 175,
            'weight' => 70,
            'medical_record_access_code' => PatientProfile::generateMedicalAccessCode(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Patient 2
        |--------------------------------------------------------------------------
        */

        $patientUser2 = User::create([
            'first_name' => 'Patient',
            'last_name' => '2',
            'phone' => '0999000005',
            'email' => 'patient2@gmail.com',
            'password' => Hash::make('password'),
            'gender' => 'female',
            'birth_date' => '1998-06-20',
            'status' => UserStatus::Active->value,
        ]);

        $patientUser2->assignRole('patient');

        PatientProfile::create([
            'user_id' => $patientUser2->id,
            'section_id' => null,
            'profile_image' => null,
            'id_card' =>  'id_cards/OmVfSdVCkIp0UPDM2AbfZ4zZ6zkwKhElvyww7hz.jpg',
            'blood_group' => 'O+',
            'tall' => 165,
            'weight' => 60,
            'medical_record_access_code' => PatientProfile::generateMedicalAccessCode(),
        ]);


        $this->command->info('Test data created successfully.');
        $this->command->info('All test users password: password');
    }
}
