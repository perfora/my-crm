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
        Schema::table('tum_isler', function (Blueprint $table) {
            $table->string('notion_id')->nullable()->unique()->after('id');
            $table->string('notion_url')->nullable()->after('notion_id');
        });

        Schema::table('musteriler', function (Blueprint $table) {
            $table->string('notion_id')->nullable()->unique()->after('id');
            $table->string('notion_url')->nullable()->after('notion_id');
        });

        Schema::table('markalar', function (Blueprint $table) {
            $table->string('notion_id')->nullable()->unique()->after('id');
            $table->string('notion_url')->nullable()->after('notion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tum_isler', function (Blueprint $table) {
            $table->dropColumn(['notion_id', 'notion_url']);
        });

        Schema::table('musteriler', function (Blueprint $table) {
            $table->dropColumn(['notion_id', 'notion_url']);
        });

        Schema::table('markalar', function (Blueprint $table) {
            $table->dropColumn(['notion_id', 'notion_url']);
        });
    }
};
