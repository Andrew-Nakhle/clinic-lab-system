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
            $table->foreignId('section_id')->nullable()->constrained()->onDelete('set null');
            $table->string('profile_image')->nullable();
            $table->string('specialization')->nullable()    ;
//            $table->string('qualification');
            $table->integer('experience_years')->nullable();
            $table->string('certification')->nullable();//enter when the doctor want to update his profile
            $table->text('bio')->nullable();//enter when the doctor want to update his profile
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
