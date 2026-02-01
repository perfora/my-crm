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
        Schema::table('musteriler', function (Blueprint $table) {
            // ENUM'dan STRING'e çevir - artık kullanıcılar yeni değerler girebilir
            $table->string('turu')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('musteriler', function (Blueprint $table) {
            // Geri dönüş için ENUM'a çevir
            $table->enum('turu', [
                'Netcom', 'Bayi', 'Resmi Kurum', 'Üniversite', 
                'Belediye', 'Hastane', 'Özel Sektör', 'Tedarikçi', 'Üretici'
            ])->nullable()->change();
        });
    }
};
