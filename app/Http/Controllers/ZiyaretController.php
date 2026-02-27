<?php

namespace App\Http\Controllers;

use App\Models\Musteri;
use App\Models\Ziyaret;
use App\Services\ExchangeEwsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZiyaretController extends Controller
{
    public function index()
    {
        return view('ziyaretler.index');
    }

    public function edit(int $id)
    {
        $ziyaret = Ziyaret::findOrFail($id);
        return view('ziyaretler.edit', compact('ziyaret'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $ziyaret = Ziyaret::findOrFail($id);

        $validated = $request->validate([
            'ziyaret_ismi' => 'sometimes|nullable|max:255',
            'musteri_id' => 'sometimes|nullable|exists:musteriler,id',
            'ziyaret_tarihi' => 'sometimes|nullable|date',
            'arama_tarihi' => 'sometimes|nullable|date',
            'tur' => 'sometimes|nullable|string',
            'durumu' => 'sometimes|nullable|string',
            'ziyaret_notlari' => 'sometimes|nullable|string',
        ]);

        if (!empty($validated['ziyaret_tarihi']) && empty($validated['durumu'])) {
            $validated['durumu'] = 'Planlandı';
        }
        if (!empty($validated['arama_tarihi']) && empty($validated['durumu'])) {
            $validated['durumu'] = 'Planlandı';
        }
        if (($validated['durumu'] ?? null) === 'Tamamlandı') {
            $validated['gerceklesen_tarih'] = $ziyaret->gerceklesen_tarih ?? Carbon::now('Europe/Istanbul');
        }

        $ziyaret->update($validated);

        if (in_array($ziyaret->durumu, ['Beklemede', 'Planlandı']) && $ziyaret->ziyaret_tarihi) {
            $subject = $ziyaret->ziyaret_ismi ?: 'Ziyaret';
            $start = crmToIstanbulCarbon($ziyaret->ziyaret_tarihi);
            $end = $start->copy()->addMinutes(30);
            $body = $ziyaret->ziyaret_notlari ?? '';
            $ews = app(ExchangeEwsService::class);
            $result = $ews->createOrUpdateVisitEvent(
                $ziyaret->ews_item_id,
                $ziyaret->ews_change_key,
                $subject,
                $start,
                $end,
                $body
            );
            if (empty($result['error']) && !empty($result['item_id'])) {
                $ziyaret->update([
                    'ews_item_id' => $result['item_id'],
                    'ews_change_key' => $result['change_key'] ?? $ziyaret->ews_change_key,
                ]);
            }
        }

        return redirect('/ziyaretler')->with('message', 'Ziyaret güncellendi.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $ziyaret = Ziyaret::findOrFail($id);
        if ($ziyaret->ews_item_id) {
            try {
                $ews = app(ExchangeEwsService::class);
                $ews->deleteVisitEvent($ziyaret->ews_item_id, $ziyaret->ews_change_key);
            } catch (\Throwable $e) {
                Log::warning('EWS delete failed for ziyaret', [
                    'id' => $ziyaret->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        $ziyaret->delete();

        return redirect('/ziyaretler')->with('message', 'Ziyaret silindi.');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ziyaret_ismi' => 'nullable|max:255',
            'musteri_id' => 'nullable|exists:musteriler,id',
            'ziyaret_tarihi' => 'nullable|date',
            'arama_tarihi' => 'nullable|date',
            'tur' => 'nullable|string',
            'durumu' => 'nullable|string',
            'ziyaret_notlari' => 'nullable|string',
            'notlar' => 'nullable|string',
        ]);

        if (isset($validated['notlar'])) {
            $validated['ziyaret_notlari'] = $validated['notlar'];
            unset($validated['notlar']);
        }

        if (empty($validated['ziyaret_ismi']) && !empty($validated['musteri_id'])) {
            $musteri = Musteri::find($validated['musteri_id']);
            $validated['ziyaret_ismi'] = $musteri ? $musteri->sirket . ' Ziyareti' : 'Ziyaret';
        }

        if (!empty($validated['ziyaret_tarihi']) && empty($validated['durumu'])) {
            $validated['durumu'] = 'Planlandı';
        }
        if (!empty($validated['arama_tarihi']) && empty($validated['durumu'])) {
            $validated['durumu'] = 'Planlandı';
        }
        if (($validated['durumu'] ?? null) === 'Tamamlandı' && empty($validated['gerceklesen_tarih'])) {
            $validated['gerceklesen_tarih'] = Carbon::now('Europe/Istanbul');
        }

        $ziyaret = Ziyaret::create($validated);

        if ($request->ajax()) {
            $musteri = null;
            if (!empty($ziyaret->musteri_id)) {
                $musteri = Musteri::find($ziyaret->musteri_id);
            }
            return response()->json([
                'id' => $ziyaret->id,
                'musteri' => $musteri ? ['id' => $musteri->id, 'sirket' => $musteri->sirket] : null,
            ]);
        }

        if (in_array($ziyaret->durumu, ['Beklemede', 'Planlandı']) && $ziyaret->ziyaret_tarihi) {
            $subject = $ziyaret->ziyaret_ismi ?: 'Ziyaret';
            $start = crmToIstanbulCarbon($ziyaret->ziyaret_tarihi);
            $end = $start->copy()->addMinutes(30);
            $body = $ziyaret->ziyaret_notlari ?? '';
            $ews = app(ExchangeEwsService::class);
            $result = $ews->createOrUpdateVisitEvent(
                null,
                null,
                $subject,
                $start,
                $end,
                $body
            );
            if (empty($result['error']) && !empty($result['item_id'])) {
                $ziyaret->update([
                    'ews_item_id' => $result['item_id'],
                    'ews_change_key' => $result['change_key'] ?? null,
                ]);
            }
        }

        if (str_contains($request->header('referer', ''), '/mobile')) {
            return redirect('/mobile')->with('message', 'Ziyaret başarıyla eklendi.');
        }

        return redirect('/ziyaretler')->with('message', 'Ziyaret başarıyla eklendi.');
    }
}
