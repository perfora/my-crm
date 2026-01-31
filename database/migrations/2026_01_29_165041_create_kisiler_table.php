<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kisiler', function (Blueprint $table) {
            $table->id();
            $table->string('ad_soyad'); // Ad Soyad (title)
            $table->string('telefon_numarasi')->nullable();
            $table->string('email_adresi')->nullable();
            $table->string('bolum')->nullable();
            $table->string('gorev')->nullable();
            
            // Firma ile ilişki
            $table->foreignId('musteri_id')->nullable()->constrained('musteriler')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kisiler');
    }
};