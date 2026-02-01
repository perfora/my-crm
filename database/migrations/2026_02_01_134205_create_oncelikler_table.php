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
        Schema::create('oncelikler', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('sira')->nullable();
            $table->timestamps();
        });
        
        // Mevcut verileri ekle
        DB::table('oncelikler')->insert([
            ['name' => '1', 'sira' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '2', 'sira' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '3', 'sira' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '4', 'sira' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oncelikler');
    }
};
