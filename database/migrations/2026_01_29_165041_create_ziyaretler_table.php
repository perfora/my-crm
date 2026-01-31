<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ziyaretler', function (Blueprint $table) {
            $table->id();
            $table->string('ziyaret_ismi'); // Ziyaret İsmi (title)
            
            // Müşteri ile ilişki
            $table->foreignId('musteri_id')->nullable()->constrained('musteriler')->onDelete('set null');
            
            // Tarihler
            $table->dateTime('ziyaret_tarihi')->nullable(); // Ziyaret Tarihi (datetime)
            $table->date('arama_tarihi')->nullable(); // Arama Tarihi (date)
            
            // Tür: Ziyaret, Telefon
            $table->enum('tur', ['Ziyaret', 'Telefon'])->nullable();
            
            // Durumu: Beklemede, Planlandı, Tamamlandı
            $table->enum('durumu', ['Beklemede', 'Planlandı', 'Tamamlandı'])->nullable();
            
            // Notlar
            $table->text('ziyaret_notlari')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ziyaretler');
    }
};