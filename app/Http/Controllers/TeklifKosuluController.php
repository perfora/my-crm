<?php

namespace App\Http\Controllers;

use App\Models\TeklifKosulu;
use Illuminate\Http\Request;

class TeklifKosuluController extends Controller
{
    public function index()
    {
        $kosullar = TeklifKosulu::orderBy('sira')->orderBy('baslik')->get();
        return view('teklif-kosullari.index', compact('kosullar'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'baslik' => 'required|string|max:255',
            'icerik' => 'required|string',
            'sira' => 'nullable|integer',
            'varsayilan' => 'boolean'
        ]);

        // Eğer bu varsayılan yapılıyorsa, diğerlerini kaldır
        if ($request->varsayilan) {
            TeklifKosulu::where('varsayilan', true)->update(['varsayilan' => false]);
        }

        TeklifKosulu::create([
            'baslik' => $validated['baslik'],
            'icerik' => $validated['icerik'],
            'sira' => $validated['sira'] ?? 0,
            'varsayilan' => $validated['varsayilan'] ?? false
        ]);

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        $kosul = TeklifKosulu::findOrFail($id);
        return response()->json($kosul);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'baslik' => 'required|string|max:255',
            'icerik' => 'required|string',
            'sira' => 'nullable|integer',
            'varsayilan' => 'boolean'
        ]);

        $kosul = TeklifKosulu::findOrFail($id);

        // Eğer bu varsayılan yapılıyorsa, diğerlerini kaldır
        if ($request->varsayilan) {
            TeklifKosulu::where('id', '!=', $id)
                ->where('varsayilan', true)
                ->update(['varsayilan' => false]);
        }

        $kosul->update([
            'baslik' => $validated['baslik'],
            'icerik' => $validated['icerik'],
            'sira' => $validated['sira'] ?? 0,
            'varsayilan' => $validated['varsayilan'] ?? false
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $kosul = TeklifKosulu::findOrFail($id);
        $kosul->delete();
        return response()->json(['success' => true]);
    }

    public function varsayilanYap($id)
    {
        // Önce tüm varsayılanları kaldır
        TeklifKosulu::where('varsayilan', true)->update(['varsayilan' => false]);
        
        // Seçileni varsayılan yap
        $kosul = TeklifKosulu::findOrFail($id);
        $kosul->update(['varsayilan' => true]);
        
        return response()->json(['success' => true]);
    }

    public function apiList()
    {
        $kosullar = TeklifKosulu::orderBy('sira')->orderBy('baslik')->get();
        return response()->json($kosullar);
    }
}
