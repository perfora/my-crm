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
            if (!Schema::hasColumn('musteriler', 'arama_periyodu_gun')) {
                $table->unsignedSmallInteger('arama_periyodu_gun')->nullable()->after('turu');
            }
            if (!Schema::hasColumn('musteriler', 'ziyaret_periyodu_gun')) {
                $table->unsignedSmallInteger('ziyaret_periyodu_gun')->nullable()->after('arama_periyodu_gun');
            }
            if (!Schema::hasColumn('musteriler', 'temas_kurali')) {
                $table->string('temas_kurali', 50)->nullable()->after('ziyaret_periyodu_gun');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('musteriler', function (Blueprint $table) {
            if (Schema::hasColumn('musteriler', 'temas_kurali')) {
                $table->dropColumn('temas_kurali');
            }
            if (Schema::hasColumn('musteriler', 'ziyaret_periyodu_gun')) {
                $table->dropColumn('ziyaret_periyodu_gun');
            }
            if (Schema::hasColumn('musteriler', 'arama_periyodu_gun')) {
                $table->dropColumn('arama_periyodu_gun');
            }
        });
    }
};

