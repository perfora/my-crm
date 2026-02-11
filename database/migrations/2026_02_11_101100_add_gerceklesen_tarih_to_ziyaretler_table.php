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
        if (!Schema::hasColumn('ziyaretler', 'gerceklesen_tarih')) {
            Schema::table('ziyaretler', function (Blueprint $table) {
                $table->dateTime('gerceklesen_tarih')->nullable()->after('arama_tarihi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ziyaretler', 'gerceklesen_tarih')) {
            Schema::table('ziyaretler', function (Blueprint $table) {
                $table->dropColumn('gerceklesen_tarih');
            });
        }
    }
};

