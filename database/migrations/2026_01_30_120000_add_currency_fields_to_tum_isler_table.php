<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tum_isler', function (Blueprint $table) {
            $table->string('teklif_doviz')->nullable()->after('teklif_tutari');
            $table->decimal('teklif_tutari_orj', 15, 2)->nullable()->after('teklif_doviz');
            $table->string('alis_doviz')->nullable()->after('alis_tutari');
            $table->decimal('alis_tutari_orj', 15, 2)->nullable()->after('alis_doviz');
            $table->decimal('orj_kur', 12, 4)->nullable()->after('kur');
        });
    }

    public function down()
    {
        Schema::table('tum_isler', function (Blueprint $table) {
            $table->dropColumn(['teklif_doviz', 'teklif_tutari_orj', 'alis_doviz', 'alis_tutari_orj', 'orj_kur']);
        });
    }
};
