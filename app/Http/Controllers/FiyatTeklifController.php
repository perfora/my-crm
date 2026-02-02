<?php

namespace App\Http\Controllers;

use App\Models\FiyatTeklif;
use App\Models\TeklifKalem;
use App\Models\Musteri;
use App\Models\Kisi;
use App\Models\Urun;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FiyatTeklifController extends Controller
{
    public function index()
    {
        $teklifler = FiyatTeklif::with('musteri')
            ->orderBy('tarih', 'desc')
            ->get();
        
        return view('fiyat-teklifleri.index', compact('teklifler'));
    }

    public function create()
    {
        $musteriler = Musteri::where('turu', '!=', 'Tedarikçi')
            ->orderBy('sirket')
            ->get();
        
        $tedarikciler = Musteri::where('turu', 'Tedarikçi')
            ->orderBy('sirket')
            ->get();
        
        $urunler = Urun::with('marka')->orderBy('urun_adi')->get();
        
        // Yeni teklif numarası oluştur
        $teklifNo = 'T-' . date('Y') . '-' . str_pad(FiyatTeklif::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT);
        
        return view('fiyat-teklifleri.create', compact('musteriler', 'tedarikciler', 'urunler', 'teklifNo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teklif_no' => 'required|unique:fiyat_teklifleri,teklif_no',
            'musteri_id' => 'required|exists:musteriler,id',
            'yetkili_adi' => 'nullable|string',
            'yetkili_email' => 'nullable|email',
            'tarih' => 'required|date',
            'gecerlilik_tarihi' => 'nullable|date',
            'giris_metni' => 'nullable|string',
            'ek_notlar' => 'nullable|string',
            'teklif_kosullari' => 'nullable|string',
            'kar_orani_varsayilan' => 'nullable|integer|min:0',
            'kalemler' => 'required|array|min:1',
            'kalemler.*.musteri_id' => 'nullable|exists:musteriler,id',
            'kalemler.*.urun_id' => 'nullable|exists:urunler,id',
            'kalemler.*.urun_adi' => 'required|string',
            'kalemler.*.alis_fiyat' => 'required|numeric|min:0',
            'kalemler.*.adet' => 'required|integer|min:1',
            'kalemler.*.kar_orani' => 'required|integer',
            'kalemler.*.para_birimi' => 'required|string',
        ]);

        // Teklif oluştur
        $teklif = FiyatTeklif::create([
            'teklif_no' => $validated['teklif_no'],
            'musteri_id' => $validated['musteri_id'],
            'yetkili_adi' => $validated['yetkili_adi'],
            'yetkili_email' => $validated['yetkili_email'],
            'tarih' => $validated['tarih'],
            'gecerlilik_tarihi' => $validated['gecerlilik_tarihi'],
            'durum' => 'Taslak',
            'giris_metni' => $validated['giris_metni'],
            'ek_notlar' => $validated['ek_notlar'],
            'teklif_kosullari' => $validated['teklif_kosullari'],
            'kar_orani_varsayilan' => $validated['kar_orani_varsayilan'] ?? 25,
        ]);

        // Kalemleri ekle ve hesapla
        foreach ($validated['kalemler'] as $index => $kalemData) {
            // Ürün yoksa oluştur
            $urun = null;
            if (!empty($kalemData['urun_id'])) {
                $urun = Urun::find($kalemData['urun_id']);
            } else {
                // Yeni ürün oluştur
                $urun = Urun::create([
                    'urun_adi' => $kalemData['urun_adi'],
                    'son_alis_fiyat' => $kalemData['alis_fiyat'],
                    'ortalama_kar_orani' => $kalemData['kar_orani'],
                ]);
                
                // Eğer tedarikçi seçildiyse fiyat kaydı da oluştur
                if (!empty($kalemData['musteri_id'])) {
                    \App\Models\TedarikiciFiyat::create([
                        'musteri_id' => $kalemData['musteri_id'],
                        'urun_id' => $urun->id,
                        'urun_adi' => $urun->urun_adi,
                        'tarih' => $validated['tarih'],
                        'birim_fiyat' => $kalemData['alis_fiyat'],
                        'para_birimi' => $kalemData['para_birimi'],
                        'minimum_siparis' => $kalemData['adet'],
                        'aktif' => true,
                    ]);
                }
            }
            
            $kalem = new TeklifKalem([
                'teklif_id' => $teklif->id,
                'musteri_id' => $kalemData['musteri_id'],
                'urun_id' => $urun?->id,
                'sira' => $index + 1,
                'urun_adi' => $kalemData['urun_adi'],
                'alis_fiyat' => $kalemData['alis_fiyat'],
                'adet' => $kalemData['adet'],
                'kar_orani' => $kalemData['kar_orani'],
                'para_birimi' => $kalemData['para_birimi'],
            ]);
            $kalem->hesapla();
        }

        // Toplamları güncelle
        $teklif->hesaplaToplamlar();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Teklif oluşturuldu.',
                'teklif_id' => $teklif->id
            ]);
        }

        return redirect('/fiyat-teklifleri')->with('message', 'Teklif oluşturuldu.');
    }

    public function show($id)
    {
        $teklif = FiyatTeklif::with(['musteri', 'kalemler.tedarikci', 'kalemler.urun'])
            ->findOrFail($id);
        
        return view('fiyat-teklifleri.show', compact('teklif'));
    }

    public function destroy($id)
    {
        $teklif = FiyatTeklif::findOrFail($id);
        $teklif->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Teklif silindi.']);
        }

        return redirect('/fiyat-teklifleri')->with('message', 'Teklif silindi.');
    }

    public function getYetkililer($musteriId)
    {
        $yetkililer = Kisi::where('musteri_id', $musteriId)
            ->select('id', 'ad_soyad', 'email_adresi')
            ->get();
        
        return response()->json($yetkililer);
    }
}
