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
<<<<<<< HEAD
<<<<<<< HEAD
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('secretary_id')->nullable();
            $table->date('date');
            $table->time('time');
            $table->string('status')->default('pending');
=======
            //$table->foreignId('patient_id')->constrained()->onDelete('cascade');
            //$table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            //$table->foreignId('secretary_id')->nullable()->constrained()->onDelete('set null');
            $table->string('made_by');
            $table->dateTime('appointment_date');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->decimal('price');
            $table->string('price');
>>>>>>> cbf2b73a062e6a4a087972bd7a80a9052966c2dd
=======
            $table->foreignId('patient_id')->constrained('patient_profiles')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctor_profiles')->onDelete('cascade');
            $table->foreignId('secretary_id')->nullable()->constrained('secretary_profiles')->onDelete('set null');

            $table->string('made_by');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('status')->default('booked');
            $table->decimal('price',8,2);

>>>>>>> 347058423acfaa612372eae2f94fca8a80374f55
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('secretary_id')->references('id')->on('users')->onDelete('set null');
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
