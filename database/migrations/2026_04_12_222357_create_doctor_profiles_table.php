<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();

            // العلاقات والربط
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();

            // البيانات الشخصية والمهنية للطبيب
            $table->string('specialization')->nullable();
            $table->string('qualification')->nullable();
            $table->integer('experience_years')->nullable();
            $table->text('bio')->nullable();
            $table->string('certification')->nullable();
            $table->string('profile_image')->nullable();

            // الأعمدة المالية المضافة من كود زميلك
            $table->decimal('consultation_fee', 8, 2)->nullable();
            $table->decimal('home_visit_fee', 8, 2)->nullable();
            $table->decimal('monthly_salary', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
