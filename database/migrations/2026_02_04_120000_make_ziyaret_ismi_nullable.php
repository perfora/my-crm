<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Önce tablo yapısını kontrol et
        $columns = DB::select("PRAGMA table_info(ziyaretler)");
        $columnNames = array_map(fn($col) => $col->name, $columns);
        
        // SQLite için geçici tablo yöntemi kullanıyoruz
        DB::statement('CREATE TABLE ziyaretler_temp AS SELECT * FROM ziyaretler');
        
        // Eski tabloyu sil
        Schema::dropIfExists('ziyaretler');
        
        // Yeni tabloyu oluştur (ziyaret_ismi nullable)
        Schema::create('ziyaretler', function (Blueprint $table) {
            $table->id();
            $table->string('ziyaret_ismi')->nullable(); // Nullable yaptık
            $table->foreignId('musteri_id')->nullable()->constrained('musteriler')->onDelete('set null');
            $table->dateTime('ziyaret_tarihi')->nullable();
            $table->date('arama_tarihi')->nullable();
            $table->enum('tur', ['Ziyaret', 'Telefon'])->nullable();
            $table->enum('durumu', ['Beklemede', 'Planlandı', 'Tamamlandı'])->nullable();
            $table->text('ziyaret_notlari')->nullable();
            $table->string('ews_item_id')->nullable();
            $table->string('ews_change_key')->nullable();
            $table->timestamps();
        });
        
        // Kolonları listele ve verileri geri yükle
        $insertColumns = implode(', ', $columnNames);
        DB::statement("INSERT INTO ziyaretler ({$insertColumns}) SELECT {$insertColumns} FROM ziyaretler_temp");
        
        // Geçici tabloyu sil
        DB::statement('DROP TABLE ziyaretler_temp');
    }

    public function down(): void
    {
        // Geri alma için benzer yöntem
        Schema::table('ziyaretler', function (Blueprint $table) {
            DB::statement('CREATE TABLE ziyaretler_temp AS SELECT * FROM ziyaretler');
            Schema::dropIfExists('ziyaretler');
        });
        
        Schema::create('ziyaretler', function (Blueprint $table) {
            $table->id();
            $table->string('ziyaret_ismi'); // NOT NULL
            $table->foreignId('musteri_id')->nullable()->constrained('musteriler')->onDelete('set null');
            $table->dateTime('ziyaret_tarihi')->nullable();
            $table->date('arama_tarihi')->nullable();
            $table->enum('tur', ['Ziyaret', 'Telefon'])->nullable();
            $table->enum('durumu', ['Beklemede', 'Planlandı', 'Tamamlandı'])->nullable();
            $table->text('ziyaret_notlari')->nullable();
            $table->string('ews_item_id')->nullable();
            $table->string('ews_change_key')->nullable();
            $table->timestamps();
        });
        
        DB::statement('INSERT INTO ziyaretler SELECT * FROM ziyaretler_temp');
        DB::statement('DROP TABLE ziyaretler_temp');
    }
};
