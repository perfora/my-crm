<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\Musteri;
use App\Models\TumIsler;
use App\Models\Ziyaret;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadOnlyController extends Controller
{
    public function dashboardSummary(Request $request): JsonResponse
    {
        $year = (int) $request->integer('year', (int) now()->format('Y'));

        $isler = TumIsler::query()
            ->whereYear('created_at', $year);

        $toplamTeklif = (clone $isler)->sum('teklif_tutari');
        $toplamAlis = (clone $isler)->sum('alis_tutari');
        $toplamKar = (float) $toplamTeklif - (float) $toplamAlis;

        return response()->json([
            'success' => true,
            'data' => [
                'year' => $year,
                'counts' => [
                    'musteriler' => Musteri::count(),
                    'isler' => (clone $isler)->count(),
                    'ziyaretler' => Ziyaret::count(),
                ],
                'totals' => [
                    'teklif' => round((float) $toplamTeklif, 2),
                    'alis' => round((float) $toplamAlis, 2),
                    'kar' => round((float) $toplamKar, 2),
                ],
            ],
        ]);
    }

    public function tumIsler(Request $request): JsonResponse
    {
        $query = TumIsler::with(['musteri:id,sirket', 'marka:id,name'])
            ->orderByDesc('created_at');

        if ($request->filled('tipi')) {
            $query->where('tipi', $request->string('tipi'));
        }

        if ($request->filled('durum')) {
            $query->where('register_durum', $request->string('durum'));
        }

        if ($request->filled('musteri_id')) {
            $query->where('musteri_id', $request->integer('musteri_id'));
        }

        $limit = min(max((int) $request->integer('limit', 100), 1), 500);

        $records = $query->limit($limit)->get()->map(function (TumIsler $is) {
            return [
                'id' => $is->id,
                'name' => $is->name,
                'musteri' => $is->musteri?->sirket,
                'marka' => $is->marka?->name,
                'tipi' => $is->tipi,
                'register_durum' => $is->register_durum,
                'turu' => $is->turu,
                'oncelik' => $is->oncelik,
                'teklif_tutari' => $is->teklif_tutari,
                'alis_tutari' => $is->alis_tutari,
                'kapanis_tarihi' => optional($is->kapanis_tarihi)?->format('Y-m-d'),
                'lisans_bitis' => optional($is->lisans_bitis)?->format('Y-m-d'),
                'is_guncellenme_tarihi' => optional($is->is_guncellenme_tarihi)?->format('Y-m-d'),
                'created_at' => optional($is->created_at)?->toIso8601String(),
                'updated_at' => optional($is->updated_at)?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $records,
            'meta' => [
                'count' => $records->count(),
                'limit' => $limit,
            ],
        ]);
    }

    public function musteriler(Request $request): JsonResponse
    {
        $query = Musteri::query()->orderBy('sirket');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where('sirket', 'like', $term);
        }

        if ($request->filled('derece')) {
            $query->where('derece', $request->string('derece'));
        }

        if ($request->filled('turu')) {
            $query->where('turu', $request->string('turu'));
        }

        $limit = min(max((int) $request->integer('limit', 100), 1), 500);

        $records = $query->limit($limit)->get()->map(function (Musteri $musteri) {
            return [
                'id' => $musteri->id,
                'sirket' => $musteri->sirket,
                'sehir' => $musteri->sehir,
                'telefon' => $musteri->telefon,
                'derece' => $musteri->derece,
                'turu' => $musteri->turu,
                'arama_periyodu_gun' => $musteri->arama_periyodu_gun,
                'ziyaret_periyodu_gun' => $musteri->ziyaret_periyodu_gun,
                'temas_kurali' => $musteri->temas_kurali,
                'created_at' => optional($musteri->created_at)?->toIso8601String(),
                'updated_at' => optional($musteri->updated_at)?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $records,
            'meta' => [
                'count' => $records->count(),
                'limit' => $limit,
            ],
        ]);
    }

    public function ziyaretler(Request $request): JsonResponse
    {
        $query = Ziyaret::with('musteri:id,sirket')->orderByDesc('gerceklesen_tarih')->orderByDesc('id');

        if ($request->filled('durumu')) {
            $query->where('durumu', $request->string('durumu'));
        }

        if ($request->filled('tur')) {
            $query->where('tur', $request->string('tur'));
        }

        if ($request->filled('musteri_id')) {
            $query->where('musteri_id', $request->integer('musteri_id'));
        }

        $limit = min(max((int) $request->integer('limit', 100), 1), 500);

        $records = $query->limit($limit)->get()->map(function (Ziyaret $ziyaret) {
            return [
                'id' => $ziyaret->id,
                'ziyaret_ismi' => $ziyaret->ziyaret_ismi,
                'musteri' => $ziyaret->musteri?->sirket,
                'tur' => $ziyaret->tur,
                'durumu' => $ziyaret->durumu,
                'ziyaret_tarihi' => optional($ziyaret->ziyaret_tarihi)?->toIso8601String(),
                'arama_tarihi' => optional($ziyaret->arama_tarihi)?->toIso8601String(),
                'gerceklesen_tarih' => optional($ziyaret->gerceklesen_tarih)?->toIso8601String(),
                'ziyaret_notlari' => $ziyaret->ziyaret_notlari,
                'created_at' => optional($ziyaret->created_at)?->toIso8601String(),
                'updated_at' => optional($ziyaret->updated_at)?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $records,
            'meta' => [
                'count' => $records->count(),
                'limit' => $limit,
            ],
        ]);
    }
}

