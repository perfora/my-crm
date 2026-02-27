<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TumIsler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WidgetDataController extends Controller
{
    public function filter(Request $request): JsonResponse
    {
        $filterData = $request->input('filterData', []);
        $query = TumIsler::query();

        if (isset($filterData['name'])) {
            $query->where('name', 'LIKE', '%' . $filterData['name'] . '%');
        }

        if (isset($filterData['tipi'])) {
            $query->where('tipi', $filterData['tipi']);
        }

        if (isset($filterData['yil'])) {
            $tipi = isset($filterData['tipi']) ? $filterData['tipi'] : null;
            if (in_array($tipi, ['Kazanıldı', 'Kaybedildi'])) {
                $query->whereYear('kapanis_tarihi', $filterData['yil']);
            } else {
                $query->whereYear('is_guncellenme_tarihi', $filterData['yil']);
            }
        }

        if (isset($filterData['turu'])) {
            $query->where('turu', $filterData['turu']);
        }
        if (isset($filterData['oncelik'])) {
            $query->where('oncelik', $filterData['oncelik']);
        }
        if (isset($filterData['register_durum'])) {
            $query->where('register_durum', $filterData['register_durum']);
        }
        if (isset($filterData['musteri_id'])) {
            $query->where('musteri_id', $filterData['musteri_id']);
        }
        if (isset($filterData['marka_id'])) {
            $query->where('marka_id', $filterData['marka_id']);
        }

        if (isset($filterData['teklif_min'])) {
            $query->where('teklif_tutari', '>=', $filterData['teklif_min']);
        }
        if (isset($filterData['teklif_max'])) {
            $query->where('teklif_tutari', '<=', $filterData['teklif_max']);
        }
        if (isset($filterData['alis_min'])) {
            $query->where('alis_tutari', '>=', $filterData['alis_min']);
        }
        if (isset($filterData['alis_max'])) {
            $query->where('alis_tutari', '<=', $filterData['alis_max']);
        }

        if (isset($filterData['acilis_start'])) {
            $query->whereDate('is_guncellenme_tarihi', '>=', $filterData['acilis_start']);
        }
        if (isset($filterData['acilis_end'])) {
            $query->whereDate('is_guncellenme_tarihi', '<=', $filterData['acilis_end']);
        }
        if (isset($filterData['kapanis_start'])) {
            $query->whereDate('kapanis_tarihi', '>=', $filterData['kapanis_start']);
        }
        if (isset($filterData['kapanis_end'])) {
            $query->whereDate('kapanis_tarihi', '<=', $filterData['kapanis_end']);
        }
        if (isset($filterData['lisans_start'])) {
            $query->whereDate('lisans_bitis', '>=', $filterData['lisans_start']);
        }
        if (isset($filterData['lisans_end'])) {
            $query->whereDate('lisans_bitis', '<=', $filterData['lisans_end']);
        }
        if (isset($filterData['updated_start'])) {
            $query->whereDate('updated_at', '>=', $filterData['updated_start']);
        }
        if (isset($filterData['updated_end'])) {
            $query->whereDate('updated_at', '<=', $filterData['updated_end']);
        }

        $isler = $query->get();

        if (isset($filterData['kar_min']) || isset($filterData['kar_max'])) {
            $isler = $isler->filter(function ($is) use ($filterData) {
                $kar = ($is->teklif_tutari ?? 0) - ($is->alis_tutari ?? 0);
                $minOk = !isset($filterData['kar_min']) || $kar >= $filterData['kar_min'];
                $maxOk = !isset($filterData['kar_max']) || $kar <= $filterData['kar_max'];
                return $minOk && $maxOk;
            });
        }

        $totalTeklif = 0;
        $totalAlis = 0;
        foreach ($isler as $is) {
            if ($is->teklif_doviz === 'USD' && $is->teklif_tutari) {
                $totalTeklif += $is->teklif_tutari;
            }
            if ($is->alis_doviz === 'USD' && $is->alis_tutari) {
                $totalAlis += $is->alis_tutari;
            }
        }

        $totalKar = $totalTeklif - $totalAlis;

        return response()->json([
            'count' => $isler->count(),
            'totalTeklif' => number_format($totalTeklif, 2),
            'totalKar' => number_format($totalKar, 2),
        ]);
    }
}
