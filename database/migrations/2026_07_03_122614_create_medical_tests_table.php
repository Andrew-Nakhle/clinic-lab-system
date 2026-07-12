<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // جعل الكود فريداً أفضل برمجياً
            $table->decimal('price', 8, 2)->default(0.00); // 💡 تعديل من string إلى decimal
            $table->string('display_name');
            $table->string('normal_range');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_tests');
    }
};
