<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('musteriler', function (Blueprint $table) {
            $table->id();
            $table->string('sirket'); // Şirket adı (title) - name yerine
            $table->string('sehir')->nullable();
            $table->text('adres')->nullable();
            $table->string('telefon')->nullable();
            $table->text('notlar')->nullable();
            
            // Derece: 1-Sık, 2-Orta, 3-Düşük, 4-Hiç
            $table->enum('derece', ['1 -Sık', '2 - Orta', '3- Düşük', '4 - Hiç'])->nullable();
            
            // Türü - string olarak değiştirildi, kullanıcılar yeni değer girebilsin
            $table->string('turu')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('musteriler');
    }
};