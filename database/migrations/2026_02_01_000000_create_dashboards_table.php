<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->default('Varsayılan');
            $table->boolean('is_default')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });

        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->string('type'); // 'table', 'chart', 'calendar', 'metric'
            $table->string('data_source'); // 'tum_isler', 'musteriler', 'ziyaretler', 'kisiler'
            $table->json('columns'); // Seçili sütunlar
            $table->json('filters'); // Saklanan filtreler (dinamik)
            $table->json('config')->nullable(); // Grafik ayarları, başlık, vb.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
        Schema::dropIfExists('dashboards');
    }
};
