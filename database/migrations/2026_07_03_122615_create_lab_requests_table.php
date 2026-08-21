<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_profile_id')->nullable()->constrained('doctor_profiles')->onDelete('cascade');
            $table->foreignId('patient_profile_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->foreignId('laboratory_profile_id')->nullable()->constrained('laboratory_profiles')->onDelete('set null');
            $table->text('doctor_notes')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed'])->default('pending');
            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_requests');
    }
};
