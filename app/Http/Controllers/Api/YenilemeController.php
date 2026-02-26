<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TumIsler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class YenilemeController extends Controller
{
    public function ac(Request $request): JsonResponse
    {
        $eskiIsId = $request->input('is_id');
        $eskiIs = TumIsler::findOrFail($eskiIsId);

        if (DB::table('lisans_yenileme_kayitlari')->where('source_is_id', $eskiIs->id)->exists()) {
            return response()->json([
                'success' => true,
                'already_processed' => true,
                'message' => 'Bu kayıt zaten işlendi',
            ]);
        }

        $yeniIs = new TumIsler();
        $yeniIs->name = $eskiIs->name;
        $yeniIs->musteri_id = $eskiIs->musteri_id;
        $yeniIs->marka_id = $eskiIs->marka_id;
        $yeniIs->tipi = 'Verilecek';
        $yeniIs->oncelik = 1;
        $yeniIs->teklif_tutari = $eskiIs->teklif_tutari;
        $yeniIs->teklif_doviz = $eskiIs->teklif_doviz;
        $yeniIs->lisans_bitis = null;
        $yeniIs->is_guncellenme_tarihi = now();
        $yeniIs->aciklama = 'Lisans yenileme - Önceki iş ID: ' . $eskiIs->id;
        $yeniIs->save();

        DB::table('lisans_yenileme_kayitlari')->insert([
            'source_is_id' => $eskiIs->id,
            'created_is_id' => $yeniIs->id,
            'durum' => 'created',
            'user_id' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Yenileme kaydı oluşturuldu',
            'yeni_is' => $yeniIs,
        ]);
    }

    public function isaretle(Request $request): JsonResponse
    {
        $eskiIsId = $request->input('is_id');
        TumIsler::findOrFail($eskiIsId);

        DB::table('lisans_yenileme_kayitlari')->updateOrInsert(
            ['source_is_id' => $eskiIsId],
            [
                'durum' => 'opened',
                'user_id' => auth()->id(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Kayıt işlendi',
        ]);
    }
}
