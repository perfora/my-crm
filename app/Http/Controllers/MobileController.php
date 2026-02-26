<?php

namespace App\Http\Controllers;

use App\Models\Musteri;
use App\Models\Ziyaret;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    public function index()
    {
        return view('mobile.index');
    }

    public function yeniIs()
    {
        return view('mobile.yeni-is');
    }

    public function yeniZiyaret()
    {
        return view('mobile.yeni-ziyaret');
    }

    public function planliKayitlar()
    {
        return view('mobile.planli-kayitlar');
    }

    public function hizliKayit()
    {
        return view('mobile.hizli-kayit');
    }

    public function hizliKayitStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'musteri_id' => 'required|exists:musteriler,id',
            'contact_type' => 'required|in:Telefon,Ziyaret',
            'ziyaret_notlari' => 'nullable|string',
        ]);

        $musteri = Musteri::findOrFail($validated['musteri_id']);
        $now = Carbon::now('Europe/Istanbul');
        $isTelefon = $validated['contact_type'] === 'Telefon';

        Ziyaret::create([
            'ziyaret_ismi' => $musteri->sirket . ' ' . ($isTelefon ? 'Arama' : 'Ziyaret'),
            'musteri_id' => $musteri->id,
            'ziyaret_tarihi' => $isTelefon ? null : $now,
            'arama_tarihi' => $isTelefon ? $now : null,
            'gerceklesen_tarih' => $now,
            'tur' => $validated['contact_type'],
            'durumu' => 'Tamamlandı',
            'ziyaret_notlari' => $validated['ziyaret_notlari'] ?? null,
        ]);

        return redirect('/mobile/hizli-kayit')->with('message', 'Hızlı kayıt oluşturuldu.');
    }

    public function planliKayitTamamla(Request $request, int $id): RedirectResponse
    {
        $ziyaret = Ziyaret::findOrFail($id);
        $validated = $request->validate([
            'ziyaret_notlari' => 'nullable|string',
        ]);

        $updateData = [
            'durumu' => 'Tamamlandı',
            'gerceklesen_tarih' => Carbon::now('Europe/Istanbul'),
        ];

        $newNote = trim((string) ($validated['ziyaret_notlari'] ?? ''));
        if ($newNote !== '') {
            $oldNote = trim((string) ($ziyaret->ziyaret_notlari ?? ''));
            $updateData['ziyaret_notlari'] = $oldNote === '' ? $newNote : $oldNote . "\n\n" . $newNote;
        }

        $ziyaret->update($updateData);

        return redirect('/mobile/planli-kayitlar')->with('message', 'Kayıt tamamlandı.');
    }

    public function raporlar()
    {
        return view('mobile.raporlar');
    }
}
