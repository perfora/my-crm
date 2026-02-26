<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RaporController extends Controller
{
    public function marka(Request $request): JsonResponse
    {
        $yil = $request->input('yil', date('Y'));

        $rapor = DB::select("
            SELECT
                m.name as marka,
                COUNT(t.id) as adet,
                SUM(CASE
                    WHEN t.teklif_doviz = 'TL' THEN t.teklif_tutari / 35
                    ELSE t.teklif_tutari
                END) as toplam_teklif,
                SUM(CASE
                    WHEN t.alis_doviz = 'TL' THEN t.alis_tutari / 35
                    ELSE t.alis_tutari
                END) as toplam_alis,
                SUM(CASE
                    WHEN t.teklif_doviz = 'TL' THEN t.teklif_tutari / 35
                    ELSE t.teklif_tutari
                END) - SUM(CASE
                    WHEN t.alis_doviz = 'TL' THEN t.alis_tutari / 35
                    ELSE t.alis_tutari
                END) as toplam_kar
            FROM tum_isler t
            LEFT JOIN markalar m ON t.marka_id = m.id
            WHERE t.tipi = 'Kazanıldı'
            AND strftime('%Y', t.kapanis_tarihi) = ?
            GROUP BY t.marka_id, m.name
            ORDER BY toplam_kar DESC
        ", [$yil]);

        return response()->json($rapor);
    }

    public function musteri(Request $request): JsonResponse
    {
        $yil = $request->input('yil', date('Y'));

        $rapor = DB::select("
            SELECT
                m.id as musteri_id,
                m.sirket as musteri,
                COUNT(t.id) as adet,
                SUM(CASE
                    WHEN t.teklif_doviz = 'TL' THEN t.teklif_tutari / 35
                    ELSE t.teklif_tutari
                END) as toplam_teklif,
                SUM(CASE
                    WHEN t.teklif_doviz = 'TL' THEN t.teklif_tutari / 35
                    ELSE t.teklif_tutari
                END) - SUM(CASE
                    WHEN t.alis_doviz = 'TL' THEN t.alis_tutari / 35
                    ELSE t.alis_tutari
                END) as toplam_kar,
                (SELECT COUNT(*) FROM ziyaretler z
                 WHERE z.musteri_id = m.id
                 AND z.durumu = 'Tamamlandı'
                 AND z.tur = 'Ziyaret'
                 AND strftime('%Y', z.ziyaret_tarihi) = ?) as ziyaret_adedi,
                (SELECT COUNT(*) FROM ziyaretler z
                 WHERE z.musteri_id = m.id
                 AND z.durumu = 'Tamamlandı'
                 AND z.tur = 'Telefon'
                 AND strftime('%Y', z.arama_tarihi) = ?) as arama_adedi
            FROM tum_isler t
            LEFT JOIN musteriler m ON t.musteri_id = m.id
            WHERE t.tipi = 'Kazanıldı'
            AND strftime('%Y', t.kapanis_tarihi) = ?
            GROUP BY t.musteri_id, m.sirket, m.id
            ORDER BY toplam_kar DESC
        ", [$yil, $yil, $yil]);

        return response()->json($rapor);
    }
}
