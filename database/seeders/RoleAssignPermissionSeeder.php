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
        // جلب أول قسم متاح في قاعدة البيانات لتفادي أخطاء العلاقات (Foreign Key)
        // إذا لم يكن هناك أي قسم، سيضع null لتجنب توقف السيرفر
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
        );
        $superAdminUser->assignRole('super_admin');

        /*
        |--------------------------------------------------------------------------
        | 2. إعداد صلاحيات الـ Admin وإنشاء حسابه
        |--------------------------------------------------------------------------
        */
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'create_doctors',
                'update_doctors',
                'view_doctors',
                'delete_doctors',
            ]);
        }

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

        // التحقق منعاً للتكرار إذا كان البروفايل منشأ مسبقاً
        if (!$patientUser->patient()->exists()) {
            $patientUser->patient()->create([
                'blood_group'   => 'O+',
                'weight'        => '75',
                'tall'          => '178',
                'id_card'       => 'id_cards/default_seeder_card.png',
                'profile_image' => 'profile_images/default_seeder_avatar.png',
                'section_id'    => $defaultSectionId, // تم التعديل لديناميكي آمن
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
                'section_id'     => 6, // تم التعديل لديناميكي آمن
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
                'section_id'       => 7, // تم التعديل لديناميكي آمن
                'experience_years' => 10,
                'bio'              => 'استشاري جراحة عامة وخبرة طويلة في المستشفيات التعليمية.',
                'qualification'    => 'البورد العربي في الجراحة العامة',
                'specialization'   => 'جراحة عامة مناظير',
            ]);
        }
    }
}
