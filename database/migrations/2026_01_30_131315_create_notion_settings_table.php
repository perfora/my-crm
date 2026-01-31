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
        Schema::create('notion_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, password, database_id
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Varsayılan ayarları ekle
        DB::table('notion_settings')->insert([
            ['key' => 'api_token', 'label' => 'API Token', 'value' => env('NOTION_API_TOKEN'), 'type' => 'password', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'tum_isler_db_id', 'label' => 'Tüm İşler Database ID', 'value' => null, 'type' => 'database_id', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'musteriler_db_id', 'label' => 'Müşteriler Database ID', 'value' => null, 'type' => 'database_id', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'markalar_db_id', 'label' => 'Markalar Database ID', 'value' => null, 'type' => 'database_id', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'auto_sync_enabled', 'label' => 'Otomatik Senkronizasyon', 'value' => '0', 'type' => 'checkbox', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notion_settings');
    }
};
