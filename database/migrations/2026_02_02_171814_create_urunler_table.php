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
        Schema::create('urunler', function (Blueprint $table) {
            $table->id();
            $table->string('urun_adi');
            $table->foreignId('marka_id')->nullable()->constrained('markalar');
            $table->string('kategori')->nullable();
            $table->string('stok_kodu')->nullable();
            $table->decimal('son_alis_fiyat', 15, 2)->nullable();
            $table->integer('ortalama_kar_orani')->default(25);
            $table->text('notlar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urunler');
    }
};
