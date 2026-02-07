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
        Schema::create('lisans_yenileme_kayitlari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_is_id')->unique();
            $table->unsignedBigInteger('created_is_id')->nullable();
            $table->string('durum', 20); // created | opened
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lisans_yenileme_kayitlari');
    }
};

