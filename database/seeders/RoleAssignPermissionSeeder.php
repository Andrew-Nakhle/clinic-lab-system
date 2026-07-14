<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\Section;
use Illuminate\Database\Seeder;
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
                'get_areas'
            ]);
        }

        $doctorRole = Role::where('name', 'doctor')->first();
        if ($doctorRole) {
            $doctorRole->givePermissionTo([
                'update_doctor_profile',
                'view_doctor_profile',
                'get_medical_record'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. إنشاء حساب Admin تجريبي
        |--------------------------------------------------------------------------
        */
        $adminUser = User::firstOrCreate(
            ['email' => 'adm@example.com'],
            [
                'first_name' => 'مدير',
                'last_name'  => 'النظام',
                'phone'      => '0590000000',
                'password'   => bcrypt('abc123'),
                'gender'     => 'male',
                'birth_date' => '1988-06-15',
            ]
        );
        $adminUser->assignRole('admin');

        /*
        |--------------------------------------------------------------------------
        | 3. إنشاء حساب مريض تجريبي (Patient)
        |--------------------------------------------------------------------------
        */
        $patientUser = User::firstOrCreate(
            ['email' => 'pat@example.com'],
            [
                'first_name' => 'مريض',
                'last_name'  => 'المنصور',
                'phone'      => '0591111111',
                'password'   => bcrypt('abc123'),
                'gender'     => 'male',
                'birth_date' => '1995-05-15',
            ]
        );

        $patientUser->assignRole('patient');

        if (!$patientUser->patient()->exists()) {
            $patientUser->patient()->create([
                'blood_group'   => 'O+',
                'weight'        => '75',
                'tall'          => '178',
                'id_card'       => 'id_cards/default_seeder_card.png',
                'profile_image' => 'profile_images/default_seeder_avatar.png',
                'section_id'    => $defaultSectionId,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. إنشاء حساب مخبري تجريبي (Laboratory)
        |--------------------------------------------------------------------------
        */
        $labUser = User::firstOrCreate(
            ['email' => 'lab@example.com'],
            [
                'first_name' => 'مخبري',
                'last_name'  => 'الرازي',
                'phone'      => '0595555555',
                'password'   => bcrypt('abc123'),
                'gender'     => 'male',
                'birth_date' => '1990-01-01',
            ]
        );

        $labUser->assignRole('laboratory');

        if (!$labUser->laboratory()->exists()) {
            $labUser->laboratory()->create([
                'license_number' => 'LAB-2026-XYZ99',
                'section_id'     => $defaultSectionId,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. إنشاء حساب طبيب تجريبي (Doctor)
        |--------------------------------------------------------------------------
        */
        $doctorUser = User::firstOrCreate(
            ['email' => 'doc@example.com'],
            [
                'first_name' => 'دكتور',
                'last_name'  => 'الخطيب',
                'phone'      => '0592222222',
                'password'   => bcrypt('abc123'),
                'gender'     => 'male',
                'birth_date' => '1985-04-12',
            ]
        );

        $doctorUser->assignRole('doctor');

        if (!$doctorUser->doctor()->exists()) {
            $doctorUser->doctor()->create([
                'profile_image'    => 'profile_images/default_doctor_avatar.png',
                'certification'    => 'certifications/default_certificate.png',
                'section_id'       => $defaultSectionId,
                'experience_years' => 10,
                'bio'              => 'استشاري جراحة عامة.',
                'qualification'    => 'البورد العربي',
                'specialization'   => 'جراحة عامة',
            ]);
        }
    }
}
