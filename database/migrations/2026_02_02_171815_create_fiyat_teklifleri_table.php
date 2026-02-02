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
        Schema::create('fiyat_teklifleri', function (Blueprint $table) {
            $table->id();
            $table->string('teklif_no')->unique();
            $table->foreignId('musteri_id')->constrained('musteriler');
            $table->string('yetkili_adi')->nullable();
            $table->string('yetkili_email')->nullable();
            $table->date('tarih');
            $table->date('gecerlilik_tarihi')->nullable();
            $table->string('durum')->default('Taslak');
            $table->text('giris_metni')->nullable();
            $table->text('ek_notlar')->nullable();
            $table->text('teklif_kosullari')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('imza_path')->nullable();
            $table->integer('kar_orani_varsayilan')->default(25);
            $table->decimal('toplam_alis', 15, 2)->default(0);
            $table->decimal('toplam_satis', 15, 2)->default(0);
            $table->decimal('toplam_kar', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiyat_teklifleri');
    }
};
