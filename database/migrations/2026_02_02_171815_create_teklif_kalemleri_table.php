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
        Schema::create('teklif_kalemleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teklif_id')->constrained('fiyat_teklifleri')->onDelete('cascade');
            $table->foreignId('musteri_id')->nullable()->comment('Tedarikçi')->constrained('musteriler')->onDelete('set null');
            $table->foreignId('urun_id')->nullable()->constrained('urunler');
            $table->integer('sira')->default(0);
            $table->string('urun_adi');
            $table->decimal('alis_fiyat', 15, 2);
            $table->integer('adet');
            $table->decimal('alis_toplam', 15, 2);
            $table->integer('kar_orani');
            $table->decimal('satis_fiyat', 15, 2);
            $table->decimal('satis_toplam', 15, 2);
            $table->string('para_birimi')->default('TL');
            $table->text('notlar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teklif_kalemleri');
    }
};
