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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('specialization')->nullable();
            $table->string('qualification')->nullable();
            $table->integer('experience_years')->nullable();
<<<<<<< HEAD
            $table->text('bio')->nullable();
            $table->string('certification')->nullable();
            $table->string('profile_image')->nullable();
=======
            $table->string('certification')->nullable();//enter when the doctor want to update his profile
            $table->text('bio')->nullable();//enter when the doctor want to update his profile
            $table->decimal('consultation_fee', 8, 2)->nullable();
            $table->decimal('monthly_salary', 10, 2)->nullable();
>>>>>>> 347058423acfaa612372eae2f94fca8a80374f55
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
