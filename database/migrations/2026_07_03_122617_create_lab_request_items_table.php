<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_request_id')->constrained('lab_requests')->onDelete('cascade');
            $table->foreignId('medical_test_id')->constrained('medical_tests')->onDelete('cascade');
            $table->string('result_value')->nullable(); // يملأها المخبري لاحقاً
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_request_items');
    }
};
