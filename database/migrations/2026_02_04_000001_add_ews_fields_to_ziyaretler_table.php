<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ziyaretler', 'ews_item_id')) {
            Schema::table('ziyaretler', function (Blueprint $table) {
                $table->string('ews_item_id')->nullable()->after('ziyaret_notlari');
            });
        }

        if (!Schema::hasColumn('ziyaretler', 'ews_change_key')) {
            Schema::table('ziyaretler', function (Blueprint $table) {
                $table->string('ews_change_key')->nullable()->after('ews_item_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ziyaretler', 'ews_change_key')) {
            Schema::table('ziyaretler', function (Blueprint $table) {
                $table->dropColumn('ews_change_key');
            });
        }

        if (Schema::hasColumn('ziyaretler', 'ews_item_id')) {
            Schema::table('ziyaretler', function (Blueprint $table) {
                $table->dropColumn('ews_item_id');
            });
        }
    }
};
