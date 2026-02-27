<?php

namespace App\Http\Controllers;

use App\Models\Marka;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarkaController extends Controller
{
    public function index()
    {
        return view('markalar.index');
    }

    public function show(int $id)
    {
        $marka = Marka::findOrFail($id);

        return view('markalar.show', compact('marka'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
        ]);

        $marka = Marka::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $marka]);
        }

        return redirect('/markalar')->with('message', 'Marka başarıyla eklendi.');
    }

    public function edit(int $id)
    {
        $marka = Marka::findOrFail($id);

        return view('markalar.edit', compact('marka'));
    }

    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $marka = Marka::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255',
        ]);

        $marka->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $marka]);
        }

        return redirect('/markalar')->with('message', 'Marka güncellendi.');
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $marka = Marka::findOrFail($id);
        $marka->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect('/markalar')->with('message', 'Marka silindi.');
    }
}
