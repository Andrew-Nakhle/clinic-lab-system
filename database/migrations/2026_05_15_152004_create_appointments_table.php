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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // ربط العلاقات بالجداول المخصصة (doctor_profiles, patient_profiles, secretary_profiles)
            $table->foreignId('doctor_id')->constrained('doctor_profiles')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->foreignId('secretary_id')->nullable()->constrained('secretary_profiles')->onDelete('set null');
            $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('set null');
            $table->string('appointment_type');
            $table->text('address')->nullable();

            // تفاصيل المحاضر والوقت والتاريخ المدمجة
            $table->string('made_by');
            $table->dateTime('appointment_date')->nullable(); // للحفاظ على التوافق إذا كان مستخدماً في مكان آخر
            $table->dateTime('start_at');
            $table->dateTime('end_at');

            // السعر بصيغة decimal الصحيحة
            $table->decimal('price', 8, 2);

            // حالة الموعد (دمج الحالات في enum واحد شامل)
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'booked'])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
