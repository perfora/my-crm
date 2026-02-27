<?php

namespace App\Http\Controllers;

use App\Models\Kisi;
use App\Models\Musteri;
use App\Models\TumIsler;
use App\Models\Ziyaret;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MusteriController extends Controller
{
    public function index()
    {
        return view('musteriler.index');
    }

    public function raporlar()
    {
        return view('raporlar.index');
    }

    public function import(): RedirectResponse
    {
        $csv = storage_path('app/firmalar.csv');
        $data = array_map('str_getcsv', file($csv));
        $header = array_shift($data);
        $imported = 0;

        foreach ($data as $row) {
            $record = array_combine($header, $row);
            if (!empty($record['Şirket'])) {
                Musteri::firstOrCreate(
                    ['sirket' => $record['Şirket']],
                    [
                        'sehir' => $record['Şehir'] ?? null,
                        'adres' => $record['Adres'] ?? null,
                        'telefon' => $record['Telefon'] ?? null,
                        'notlar' => $record['Notlar'] ?? null,
                        'derece' => $record['Derece'] ?? null,
                        'turu' => $record['Türü'] ?? null,
                    ]
                );
                $imported++;
            }
        }

        $total = Musteri::count();
        return redirect('/musteriler')->with('message', "✓ $imported firma kontrol edildi. Toplam: $total müşteri");
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'sirket' => 'required|max:255',
            'sehir' => 'nullable|string',
            'adres' => 'nullable|string',
            'telefon' => 'nullable|string',
            'notlar' => 'nullable|string',
            'derece' => 'nullable|string',
            'turu' => 'nullable|string',
            'arama_periyodu_gun' => 'nullable|integer|min:1|max:3650',
            'ziyaret_periyodu_gun' => 'nullable|integer|min:1|max:3650',
            'temas_kurali' => 'nullable|string|max:50',
        ]);

        $musteri = Musteri::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $musteri]);
        }

        return redirect('/musteriler')->with('message', 'Müşteri başarıyla eklendi.');
    }

    public function quickContact(Request $request, int $id): JsonResponse
    {
        $musteri = Musteri::findOrFail($id);

        $validated = $request->validate([
            'contact_type' => 'required|in:Telefon,Ziyaret',
        ]);

        $now = Carbon::now('Europe/Istanbul');
        $isTelefon = $validated['contact_type'] === 'Telefon';

        $ziyaret = Ziyaret::create([
            'ziyaret_ismi' => $musteri->sirket . ' ' . ($isTelefon ? 'Arama' : 'Ziyaret'),
            'musteri_id' => $musteri->id,
            'ziyaret_tarihi' => $isTelefon ? null : $now,
            'arama_tarihi' => $isTelefon ? $now : null,
            'gerceklesen_tarih' => $now,
            'tur' => $validated['contact_type'],
            'durumu' => 'Tamamlandı',
            'ziyaret_notlari' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hızlı kayıt oluşturuldu.',
            'data' => [
                'id' => $ziyaret->id,
                'musteri_id' => $musteri->id,
                'musteri' => $musteri->sirket,
                'contact_type' => $validated['contact_type'],
                'created_at' => $now->toDateTimeString(),
            ],
        ]);
    }

    public function quickNote(Request $request, int $id): JsonResponse
    {
        $ziyaret = Ziyaret::findOrFail($id);
        $validated = $request->validate([
            'ziyaret_notlari' => 'required|string',
        ]);

        $ziyaret->update([
            'ziyaret_notlari' => $validated['ziyaret_notlari'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Not kaydedildi.',
            'data' => [
                'id' => $ziyaret->id,
                'ziyaret_notlari' => $ziyaret->ziyaret_notlari,
            ],
        ]);
    }

    public function show(int $id)
    {
        $musteri = Musteri::findOrFail($id);
        $kisiler = Kisi::where('musteri_id', $musteri->id)->get();
        $ziyaretler = Ziyaret::where('musteri_id', $musteri->id)->orderBy('ziyaret_tarihi', 'desc')->get();
        $isler = TumIsler::where('musteri_id', $musteri->id)->get();
        $kazanilanTotal = $isler->where('tipi', 'Kazanıldı')->sum(function ($i) {
            return ($i->teklif_doviz === 'USD' || $i->alis_doviz === 'USD') ? $i->kar_tutari : 0;
        });

        return view('musteriler.show', compact('musteri', 'kisiler', 'ziyaretler', 'isler', 'kazanilanTotal'));
    }

    public function edit(int $id)
    {
        $musteri = Musteri::findOrFail($id);
        return view('musteriler.edit', compact('musteri'));
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $musteri = Musteri::findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            $validated = $request->validate([
                'sirket' => 'sometimes|required|max:255',
                'sehir' => 'nullable|string',
                'adres' => 'nullable|string',
                'telefon' => 'nullable|string',
                'notlar' => 'nullable|string',
                'derece' => 'nullable|string',
                'turu' => 'nullable|string',
                'arama_periyodu_gun' => 'nullable|integer|min:1|max:3650',
                'ziyaret_periyodu_gun' => 'nullable|integer|min:1|max:3650',
                'temas_kurali' => 'nullable|string|max:50',
            ]);

            $musteri->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Güncellendi',
                'data' => $musteri,
            ]);
        }

        $validated = $request->validate([
            'sirket' => 'required|max:255',
            'sehir' => 'nullable|string',
            'adres' => 'nullable|string',
            'telefon' => 'nullable|string',
            'notlar' => 'nullable|string',
            'derece' => 'nullable|string',
            'turu' => 'nullable|string',
            'arama_periyodu_gun' => 'nullable|integer|min:1|max:3650',
            'ziyaret_periyodu_gun' => 'nullable|integer|min:1|max:3650',
            'temas_kurali' => 'nullable|string|max:50',
        ]);

        $musteri->update($validated);

        return redirect('/musteriler')->with('message', 'Müşteri güncellendi.');
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $musteri = Musteri::findOrFail($id);
        $musteri->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Müşteri silindi.']);
        }

        return redirect('/musteriler')->with('message', 'Müşteri silindi.');
    }

    public function deleteTuru(Request $request): JsonResponse
    {
        $turu = $request->input('turu');
        Musteri::where('turu', $turu)->update(['turu' => null]);

        return response()->json(['success' => true, 'message' => 'Tür silindi.']);
    }
}
