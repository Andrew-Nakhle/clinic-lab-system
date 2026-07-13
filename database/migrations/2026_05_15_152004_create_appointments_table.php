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
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctor_profiles')->onDelete('cascade');
            $table->foreignId('secretary_id')->nullable()->constrained('secretary_profiles')->onDelete('set null');
            $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('set null');
            $table->string('appointment_type');
            $table->text('address')->nullable();

            $table->string('made_by');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('status')->default('booked');
            $table->decimal('price',8,2);

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
