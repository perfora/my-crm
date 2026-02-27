<?php

namespace App\Http\Controllers;

use App\Models\TumIsler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TumIslerController extends Controller
{
    public function index()
    {
        return view('tum-isler.index');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|max:255',
            'musteri_id' => 'nullable|exists:musteriler,id',
            'marka_id' => 'nullable|exists:markalar,id',
            'tipi' => 'nullable|string',
            'turu' => 'nullable|string',
            'oncelik' => 'nullable|string',
            'kaybedilme_nedeni' => 'nullable|string',
            'register_durum' => 'nullable|string',
            'teklif_tutari' => 'nullable|numeric',
            'teklif_doviz' => 'nullable|string',
            'alis_tutari' => 'nullable|numeric',
            'alis_doviz' => 'nullable|string',
            'kur' => 'nullable|numeric',
            'kapanis_tarihi' => 'nullable|date',
            'lisans_bitis' => 'nullable|date',
            'is_guncellenme_tarihi' => 'nullable|date',
            'notlar' => 'nullable|string',
            'gecmis_notlar' => 'nullable|string',
            'aciklama' => 'nullable|string',
        ]);

        if (empty($validated['tipi'])) {
            $validated['tipi'] = 'Verilecek';
        }
        if (empty($validated['oncelik'])) {
            $validated['oncelik'] = '1';
        }

        if (!empty($validated['teklif_tutari']) && empty($validated['teklif_doviz'])) {
            $validated['teklif_doviz'] = 'USD';
        }
        if (!empty($validated['alis_tutari']) && empty($validated['alis_doviz'])) {
            $validated['alis_doviz'] = 'USD';
        }
        if (function_exists('crmAutoFillTcmKur')) {
            $validated = crmAutoFillTcmKur($validated);
        }

        if ($request->ajax() || $request->wantsJson()) {
            $is = TumIsler::create(array_merge($validated, [
                'is_guncellenme_tarihi' => $validated['is_guncellenme_tarihi'] ?? now(),
            ]));
            $is->load(['musteri', 'marka']);
            return response()->json(['success' => true, 'data' => $is]);
        }

        TumIsler::create(array_merge($validated, [
            'is_guncellenme_tarihi' => $validated['is_guncellenme_tarihi'] ?? now(),
        ]));

        if (str_contains($request->header('referer', ''), '/mobile')) {
            return redirect('/mobile')->with('message', 'İş başarıyla eklendi.');
        }

        return redirect('/tum-isler')->with('message', 'İş başarıyla eklendi.');
    }

    public function edit(int $id)
    {
        $is = TumIsler::findOrFail($id);
        return view('tum-isler.edit', compact('is'));
    }

    public function duplicate(int $id): RedirectResponse
    {
        $is = TumIsler::findOrFail($id);

        $newIs = $is->replicate();
        $newIs->name = $is->name . ' (Kopya)';
        $newIs->is_guncellenme_tarihi = now();
        $newIs->kapanis_tarihi = null;
        $newIs->save();

        return redirect('/tum-isler/' . $newIs->id . '/edit')->with('message', 'İş kopyalandı. Düzenleyebilirsiniz.');
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $is = TumIsler::findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            $validated = $request->validate([
                'name' => 'sometimes|required|max:255',
                'musteri_id' => 'nullable|exists:musteriler,id',
                'marka_id' => 'nullable|exists:markalar,id',
                'tipi' => 'nullable|string',
                'durum' => 'nullable|string',
                'turu' => 'nullable|string',
                'oncelik' => 'nullable|string',
                'kaybedilme_nedeni' => 'nullable|string',
                'register_durum' => 'nullable|string',
                'teklif_tutari' => 'nullable|numeric',
                'teklif_doviz' => 'nullable|string',
                'alis_tutari' => 'nullable|numeric',
                'alis_doviz' => 'nullable|string',
                'kur' => 'nullable|numeric',
                'kapanis_tarihi' => 'nullable|date',
                'lisans_bitis' => 'nullable|date',
                'is_guncellenme_tarihi' => 'nullable|date',
                'notlar' => 'nullable|string',
                'gecmis_notlar' => 'nullable|string',
                'aciklama' => 'nullable|string',
            ]);

            if (array_key_exists('teklif_tutari', $validated) && !empty($validated['teklif_tutari']) && empty($validated['teklif_doviz'])) {
                $validated['teklif_doviz'] = 'USD';
            }
            if (array_key_exists('alis_tutari', $validated) && !empty($validated['alis_tutari']) && empty($validated['alis_doviz'])) {
                $validated['alis_doviz'] = 'USD';
            }
            if (function_exists('crmAutoFillTcmKur')) {
                $validated = crmAutoFillTcmKur($validated, $is);
            }

            $is->update($validated);
            $is->load(['musteri', 'marka']);

            return response()->json([
                'success' => true,
                'message' => 'Güncellendi',
                'data' => $is,
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|max:255',
            'musteri_id' => 'nullable|exists:musteriler,id',
            'marka_id' => 'nullable|exists:markalar,id',
            'tipi' => 'nullable|string',
            'turu' => 'nullable|string',
            'oncelik' => 'nullable|string',
            'kaybedilme_nedeni' => 'nullable|string',
            'register_durum' => 'nullable|string',
            'teklif_tutari' => 'nullable|numeric',
            'teklif_doviz' => 'nullable|string',
            'alis_tutari' => 'nullable|numeric',
            'alis_doviz' => 'nullable|string',
            'kur' => 'nullable|numeric',
            'kapanis_tarihi' => 'nullable|date',
            'lisans_bitis' => 'nullable|date',
            'is_guncellenme_tarihi' => 'nullable|date',
            'notlar' => 'nullable|string',
            'gecmis_notlar' => 'nullable|string',
            'aciklama' => 'nullable|string',
        ]);

        if (!empty($validated['teklif_tutari']) && empty($validated['teklif_doviz'])) {
            $validated['teklif_doviz'] = 'USD';
        }
        if (!empty($validated['alis_tutari']) && empty($validated['alis_doviz'])) {
            $validated['alis_doviz'] = 'USD';
        }
        if (function_exists('crmAutoFillTcmKur')) {
            $validated = crmAutoFillTcmKur($validated, $is);
        }

        $is->update($validated);

        return redirect('/tum-isler')->with('message', 'İş güncellendi.');
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $is = TumIsler::findOrFail($id);
        $is->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'İş silindi.']);
        }

        return redirect('/tum-isler')->with('message', 'İş silindi.');
    }
}
