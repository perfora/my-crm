<?php

namespace App\Http\Controllers;

use App\Models\IsTipi;
use App\Models\IsTuru;
use App\Models\Oncelik;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MetaDataController extends Controller
{
    public function storeIsTipi(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:is_tipleri',
        ]);

        $tip = IsTipi::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $tip]);
        }

        return back()->with('message', 'İş tipi eklendi.');
    }

    public function storeIsTuru(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:is_turleri',
        ]);

        $tur = IsTuru::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $tur]);
        }

        return back()->with('message', 'İş türü eklendi.');
    }

    public function storeOncelik(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:oncelikler',
        ]);

        $oncelik = Oncelik::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $oncelik]);
        }

        return back()->with('message', 'Öncelik eklendi.');
    }
}
