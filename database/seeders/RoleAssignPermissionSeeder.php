<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use App\Models\Report;
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
                'get_articles_by_category',
                'get_articles_by_doctor',
                'view_patients',
                'view_doctors'

            ]);
        }

        $superAdminUser = User::firstOrCreate(
            ['email' => 'super@admin.com'],
            [
                'first_name' => 'Super',
                'last_name'  => 'Admin',
                'phone'      => '0000000000', // <-- تم تصحيح اسم العمود
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
                'view_doctors_by_section',
                'get_articles_by_category',
                'get_articles_by_doctor',
                'view_patients',
            ]);
        }

        $doctorRole = Role::where('name', 'doctor')->first();
        if ($doctorRole) {
            $doctorRole->givePermissionTo([
                'update_doctor_profile',
                'view_doctor_profile',
                'get_medical_record',
                'get_medical_notes',
                'complete_appointment',
                'create_article',
                'update_article',
                'delete_article',
                'get_articles_by_category',
                'get_articles_by_doctor'
            ]);
        }
        ////patient
$patientRole=Role::where('name', 'patient')->first();
        if ($patientRole) {
            $patientRole->givePermissionTo([
                'create_appointment_by_patient',
                'availableSlots',
                'update_patient_profile',
                'get_articles_by_category',
                'get_articles_by_doctor',
                'patient_appointments',

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
            'profile_image' => null,
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
            'profile_image' => 'profile_images/2JYc3c568bFu1L3IduttA0woAUyOes2Ol7QEPzko.jpg',
            'status' => UserStatus::Active->value,
        ]);

        $doctorUser1->assignRole('doctor');

        $doctor1 = DoctorProfile::create([
            'user_id' => $doctorUser1->id,
            'section_id' => 1,
            'specialization' => 'Cardiology',
            'qualification' => 'MD',
            'experience_years' => 10,
            'bio' => 'Test Doctor 1',
            'consultation_fee' => 10.00,
            'home_visit_fee' => 20.00,
            'monthly_salary' => 1000.00,
        ]);
        $doctor1->certifications()->createMany([
            ['certification' => 'certifications/4wCyzmFUOiCzkFQUFuMRpCgfGoW1crgPqKRs0kus.jpg'],
            ['certification' => 'certifications/4wCyzmFUOiCzkFQUFuMRpCgfGoW1crgPqKRs0kus.jpg'],
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
            'email' => 'doc@example.com',
            'password' => Hash::make('abc123'),
            'gender' => 'female',
            'profile_image' => 'profile_images/2JYc3c568bFu1L3IduttA0woAUyOes2Ol7QEPzko.jpg',
            'birth_date' => '1988-08-15',
            'status' => UserStatus::Active->value,
        ]);

        $doctorUser2->assignRole('doctor');

        $doctor2 = DoctorProfile::create([
            'user_id' => $doctorUser2->id,
            'section_id' => 1,
            'specialization' => 'Dermatology',
            'qualification' => 'MD',
            'experience_years' => 7,
            'bio' => 'Test Doctor 2',
            'consultation_fee' => 15.00,
            'home_visit_fee' => 25.00,
            'monthly_salary' => 1200.00,
        ]);
        $doctor2->certifications()->create([
            'certification' => 'certifications/4wCyzmFUOiCzkFQUFuMRpCgfGoW1crgPqKRs0kus.jpg',
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

        $patient1 = User::create([
            'first_name' => 'Patient',
            'last_name' => '1',
            'phone' => '0999000004',
            'email' => 'pat@example.com',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'birth_date' => '2000-01-15',
            'profile_image' => 'profile_images/2JYc3c568bFu1L3IduttA0woAUyOes2Ol7QEPzko.jpg',
            'status' => UserStatus::Active->value,
        ]);

        $patient1->assignRole('patient');

        $patientProfile1 = PatientProfile::create([
            'user_id' => $patient1->id,
            'section_id' => null,
            'id_card' => 'id_cards/0MavUfSdVCkIp0UPDM2AbfZ4zZ6zkwKhElwyw7hz.jpg',
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

        $patient2 = User::create([
            'first_name' => 'Patient',
            'last_name' => '2',
            'phone' => '0999000005',
            'email' => 'patient2@gmail.com',
            'password' => Hash::make('password'),
            'gender' => 'female',
            'birth_date' => '1998-06-20',
            'profile_image' => 'profile_images/2JYc3c568bFu1L3IduttA0woAUyOes2Ol7QEPzko.jpg',
            'status' => UserStatus::Active->value,
        ]);

        $patient2->assignRole('patient');

        $patientProfile2 = PatientProfile::create([
            'user_id' => $patient2->id,
            'section_id' => null,
            'id_card' => 'id_cards/0MavUfSdVCkIp0UPDM2AbfZ4zZ6zkwKhElwyw7hz.jpg',
            'blood_group' => 'O+',
            'tall' => 165,
            'weight' => 60,
            'medical_record_access_code' => PatientProfile::generateMedicalAccessCode(),
        ]);

        /*
|--------------------------------------------------------------------------
| Appointments - Patient 1 with Doctor 1
|--------------------------------------------------------------------------
*/

        $appointment1 = Appointment::create([
            'doctor_id' => $doctor1->id,
            'patient_id' => $patientProfile1->id,
            'start_at' => '2026-08-18 12:00:00',
            'end_at' => '2026-08-18 12:30:00',
            'price' => $doctor1->consultation_fee,
            'made_by' => 'patient',
            'status' => 'booked',
            'appointment_type' => 'clinic',
        ]);

        $appointment2 = Appointment::create([
            'doctor_id' => $doctor1->id,
            'patient_id' => $patientProfile1->id,
            'start_at' => '2026-08-19 13:00:00',
            'end_at' => '2026-08-19 13:30:00',
            'price' => $doctor1->consultation_fee,
            'made_by' => 'patient',
            'status' => 'booked',
            'appointment_type' => 'clinic',
        ]);

        $appointment3 = Appointment::create([
            'doctor_id' => $doctor1->id,
            'patient_id' => $patientProfile1->id,
            'start_at' => '2026-08-20 14:00:00',
            'end_at' => '2026-08-20 14:30:00',
            'price' => $doctor1->consultation_fee,
            'made_by' => 'patient',
            'status' => 'booked',
            'appointment_type' => 'clinic',
        ]);

        /*
|--------------------------------------------------------------------------
| Appointments - Patient 2 with Doctor 1
|--------------------------------------------------------------------------
*/

        $appointment4 = Appointment::create([
            'doctor_id' => $doctor1->id,
            'patient_id' =>$patientProfile2->id,
            'start_at' => '2026-08-18 15:00:00',
            'end_at' => '2026-08-18 15:30:00',
            'price' => $doctor1->consultation_fee,
            'made_by' => 'patient',
            'status' => 'completed',
            'appointment_type' => 'clinic',
        ]);

        $appointment5 = Appointment::create([
            'doctor_id' => $doctor1->id,
            'patient_id' => $patientProfile2->id,
            'start_at' => '2026-08-19 12:00:00',
            'end_at' => '2026-08-19 12:30:00',
            'price' => $doctor1->consultation_fee,
            'made_by' => 'patient',
            'status' => 'completed',
            'appointment_type' => 'clinic',
        ]);

        /*
|--------------------------------------------------------------------------
| Reports - Patient 1 / Doctor 1
|--------------------------------------------------------------------------
*/

        $report1=  Report::create([
            'patient_id' =>  $patientProfile1->id,
            'doctor_id' => $doctor1->id,
            'appointment_id' => $appointment1->id,
            'report' => 'Patient complains of mild chest discomfort. Recommended rest and monitoring of symptoms.',
        ]);
        $report1->images()->createMany([
            ['image' => 'id_cards/OmVfSdVCkIp0UPDM2AbfZ4zZ6zkwKhElvyww7hz.jpg'],
            ['image' => 'id_cards/OmVfSdVCkIp0UPDM2AbfZ4zZ6zkwKhElvyww7hz.jpg'],
        ]);

        $report2=   Report::create([
            'patient_id' =>  $patientProfile1->id,
            'doctor_id' => $doctor1->id,
            'appointment_id' => $appointment2->id,
            'report' => 'Patient returned for follow-up. Symptoms have improved compared to the previous visit.',
        ]);
        $report2->images()->create([
            'image' => 'id_cards/OmVfSdVCkIp0UPDM2AbfZ4zZ6zkwKhElvyww7hz.jpg',
        ]);
        Report::create([
            'patient_id' =>  $patientProfile1->id,
            'doctor_id' => $doctor1->id,
            'appointment_id' => $appointment3->id,
            'report' => 'Patient is in stable condition. No further treatment is required at this time.',
        ]);


        $this->command->info('Test data created successfully.');
        $this->command->info('All test users password: password');


        /*
        |--------------------------------------------------------------------------
        | Laboratory Technician 1
        |--------------------------------------------------------------------------
        */

        // 1. التأكد من وجود دور الـ lab_technician (أو إنشاءه إذا لزم الأمر)
        $labRole = Role::firstOrCreate(['name' => 'lab_technician']);

        // 2. إنشاء مستخدم جديد للمختبر
        $labUser = User::create([
            'first_name'    => 'Laboratory',
            'last_name'     => 'Technician',
            'phone'         => '0999000006',
            'email'         => 'lab@example.com',
            'password'      => Hash::make('password'),
            'gender'        => 'male',
            'birth_date'    => '1992-03-10',
            'profile_image' => 'profile_images/2JYc3c568bFu1L3IduttA0woAUyOes2Ol7QEPzko.jpg',
            'status'        => 'active',
        ]);

        // 3. إسناد الدور للمستخدم
        $labUser->assignRole('laboratory'); // أو المعرّف الخاص بالدور حسب نظامك

        // 4. إنشاء ملف المختبر (Laboratory Profile) باستخدام موديل LaboratoryProfile
        \App\Models\LaboratoryProfile::create([
            'user_id'        => $labUser->id,
            'section_id'     => $defaultSectionId, // القسم الافتراضي الذي تم جلبه في بداية الـ Seeder
            'license_number' => 'LAB-LIC-10045',
            'image'          => 'laboratory_images/sample_lab.jpg',
        ]);
    }

}



