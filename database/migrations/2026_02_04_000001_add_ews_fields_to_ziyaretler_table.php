<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ziyaretler', function (Blueprint $table) {
            $table->string('ews_item_id')->nullable()->after('ziyaret_notlari');
            $table->string('ews_change_key')->nullable()->after('ews_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('ziyaretler', function (Blueprint $table) {
            $table->dropColumn(['ews_item_id', 'ews_change_key']);
        });
    }
};
