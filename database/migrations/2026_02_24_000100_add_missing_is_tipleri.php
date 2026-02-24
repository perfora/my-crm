<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();
        $tipler = [
            'Kazanıldı',
            'Kaybedildi',
            'Verildi',
            'Verilecek',
            'Takip Edilecek',
            'Askıda',
            'Vazgeçildi',
            'Tamamlandı',
            'Register',
        ];

        foreach ($tipler as $tip) {
            DB::table('is_tipleri')->updateOrInsert(
                ['name' => $tip],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kasıtlı olarak silmiyoruz; kullanıcı verisine dokunmamak için no-op.
    }
};

