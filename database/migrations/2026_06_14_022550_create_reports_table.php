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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained('patient_profiles')->onDelete('set null');
            $table->foreignId('doctor_id')->nullable()->constrained('doctor_profiles')->onDelete('set null');
            $table->foreignId('appointment_id')->nullable()->unique()->constrained()->onDelete('set null');//to know this report belongs to which appointment....
            $table->longText('report');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
