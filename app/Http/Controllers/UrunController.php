<?php

namespace App\Http\Controllers;

use App\Models\Urun;
use App\Models\Marka;
use Illuminate\Http\Request;

class UrunController extends Controller
{
    public function index()
    {
        $urunler = Urun::with('marka')->orderBy('urun_adi')->get();
        $markalar = Marka::orderBy('marka_adi')->get();
        
        return view('urunler.index', compact('urunler', 'markalar'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'urun_adi' => 'required|string|max:255',
            'marka_id' => 'nullable|exists:markalar,id',
            'kategori' => 'nullable|string|max:255',
            'stok_kodu' => 'nullable|string|max:255',
            'son_alis_fiyat' => 'nullable|numeric|min:0',
            'ortalama_kar_orani' => 'nullable|integer|min:0',
            'notlar' => 'nullable|string',
        ]);

        $urun = Urun::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ürün eklendi.',
                'urun' => $urun
            ]);
        }

        return redirect('/urunler')->with('message', 'Ürün eklendi.');
    }

    public function update(Request $request, $id)
    {
        $urun = Urun::findOrFail($id);

        $validated = $request->validate([
            'urun_adi' => 'required|string|max:255',
            'marka_id' => 'nullable|exists:markalar,id',
            'kategori' => 'nullable|string|max:255',
            'stok_kodu' => 'nullable|string|max:255',
            'son_alis_fiyat' => 'nullable|numeric|min:0',
            'ortalama_kar_orani' => 'nullable|integer|min:0',
            'notlar' => 'nullable|string',
        ]);

        $urun->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ürün güncellendi.',
                'urun' => $urun->load('marka')
            ]);
        }

        return redirect('/urunler')->with('message', 'Ürün güncellendi.');
    }

    public function destroy(Request $request, $id)
    {
        $urun = Urun::findOrFail($id);
        $urun->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ürün silindi.'
            ]);
        }

        return redirect('/urunler')->with('message', 'Ürün silindi.');
    }
}
