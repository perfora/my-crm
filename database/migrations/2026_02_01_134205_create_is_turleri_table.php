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
        Schema::create('is_turleri', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        
        // Mevcut verileri ekle
        DB::table('is_turleri')->insert([
            ['name' => 'Cihaz', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Yazılım ve Lisans', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cihaz ve Lisans', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Yenileme', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Destek', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hizmet Alımı', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('is_turleri');
    }
};
