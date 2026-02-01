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
        Schema::create('is_tipleri', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        
        // Mevcut verileri ekle
        DB::table('is_tipleri')->insert([
            ['name' => 'Verilecek', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Verildi', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Takip Edilecek', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kazanıldı', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kaybedildi', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Vazgeçildi', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('is_tipleri');
    }
};
