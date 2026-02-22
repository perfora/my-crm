<?php

namespace App\Http\Controllers;

use App\Models\TedarikiciFiyat;
use App\Models\Musteri;
use App\Models\Urun;
use App\Models\Marka;
use Illuminate\Http\Request;

class TedarikiciFiyatController extends Controller
{
    public function index()
    {
        $fiyatlar = TedarikiciFiyat::with(['tedarikci', 'urun'])
            ->where('aktif', true)
            ->orderBy('tarih', 'desc')
            ->get();
        
        $tedarikciler = Musteri::where('turu', 'Tedarikçi')
            ->orderBy('sirket')
            ->get();
        
        $markalar = Marka::orderBy('name')->get();
        
        return view('tedarikci-fiyatlari.index', compact('fiyatlar', 'tedarikciler', 'markalar'));
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'musteri_id' => 'required|exists:musteriler,id',
            'items' => 'required|array',
            'items.*.urun_adi' => 'required|string',
            'items.*.birim_fiyat' => 'required|numeric|min:0',
            'items.*.para_birimi' => 'required|string',
            'items.*.adet' => 'nullable|integer|min:1',
            'items.*.marka_id' => 'nullable|exists:markalar,id',
        ]);

        $tedarikci = Musteri::findOrFail($validated['musteri_id']);
        $tarih = now()->toDateString();
        $created = [];

        foreach ($validated['items'] as $item) {
            // Ürün varsa bul, yoksa oluştur
            $urun = null;
            if (!empty($item['marka_id'])) {
                $urun = Urun::firstOrCreate(
                    [
                        'urun_adi' => $item['urun_adi'],
                        'marka_id' => $item['marka_id']
                    ],
                    [
                        'son_alis_fiyat' => $item['birim_fiyat'],
                    ]
                );
            }

            // Eski fiyatları pasif yap
            if ($urun) {
                TedarikiciFiyat::where('musteri_id', $tedarikci->id)
                    ->where('urun_id', $urun->id)
                    ->update(['aktif' => false]);
            }

            // Yeni fiyat ekle
            $fiyat = TedarikiciFiyat::create([
                'musteri_id' => $tedarikci->id,
                'urun_id' => $urun?->id,
                'urun_adi' => $item['urun_adi'],
                'tarih' => $tarih,
                'birim_fiyat' => $item['birim_fiyat'],
                'para_birimi' => $item['para_birimi'],
                'minimum_siparis' => $item['adet'] ?? 1,
                'aktif' => true,
            ]);

            $created[] = $fiyat;
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => count($created) . ' fiyat kaydedildi.',
                'items' => $created
            ]);
        }

        return redirect('/tedarikci-fiyatlari')->with('message', count($created) . ' fiyat kaydedildi.');
    }

    public function destroy($id)
    {
        $fiyat = TedarikiciFiyat::findOrFail($id);
        $fiyat->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Fiyat silindi.']);
        }

        return redirect('/tedarikci-fiyatlari')->with('message', 'Fiyat silindi.');
    }
}
