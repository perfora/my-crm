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
        Schema::create('teklif_kosullari', function (Blueprint $table) {
            $table->id();
            $table->string('baslik'); // "Standart Koşullar", "Lisans Satış Koşulları" vb.
            $table->text('icerik'); // HTML formatında koşullar
            $table->boolean('varsayilan')->default(false); // Varsayılan olarak seçili mi?
            $table->integer('sira')->default(0); // Sıralama için
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teklif_kosullari');
    }
};
