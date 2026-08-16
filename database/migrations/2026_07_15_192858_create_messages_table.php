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
        Schema::create('messages', function (Blueprint $table) {
            $table->id(); // مفتاح أساسي للرسالة
            // ربط المرسل بجدول المستخدمين
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');

            // ربط المستقبل بجدول المستخدمين
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');

            $table->text('body'); // نص الرسالة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
