<?php

namespace App\Http\Controllers;

use App\Models\Kisi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KisiController extends Controller
{
    public function index()
    {
        return view('kisiler.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ad_soyad' => 'required|max:255',
            'telefon_numarasi' => 'nullable|string',
            'email_adresi' => 'nullable|email',
            'bolum' => 'nullable|string',
            'gorev' => 'nullable|string',
            'musteri_id' => 'nullable|exists:musteriler,id',
            'url' => 'nullable|url',
        ]);

        Kisi::create($validated);

        return redirect('/kisiler')->with('message', 'Kişi başarıyla eklendi.');
    }

    public function edit(int $id)
    {
        $kisi = Kisi::findOrFail($id);
        return view('kisiler.edit', compact('kisi'));
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $kisi = Kisi::findOrFail($id);

        if ($request->ajax()) {
            $inlineField = collect(array_keys($request->all()))
                ->first(fn ($key) => !in_array($key, ['_token', '_method'], true));

            if ($inlineField !== null) {
                $kisi->update([$inlineField => $request->input($inlineField)]);

                return response()->json([
                    'success' => true,
                    'message' => 'Güncellendi',
                    'data' => $kisi,
                ]);
            }
        }

        $validated = $request->validate([
            'ad_soyad' => 'required|max:255',
            'telefon_numarasi' => 'nullable|string',
            'email_adresi' => 'nullable|email',
            'bolum' => 'nullable|string',
            'gorev' => 'nullable|string',
            'musteri_id' => 'nullable|exists:musteriler,id',
            'url' => 'nullable|url',
        ]);

        $kisi->update($validated);

        return redirect('/kisiler')->with('message', 'Kişi güncellendi.');
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $kisi = Kisi::findOrFail($id);
        $kisi->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Kişi silindi.']);
        }

        return redirect('/kisiler')->with('message', 'Kişi silindi.');
    }
}
