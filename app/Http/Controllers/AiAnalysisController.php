<?php

namespace App\Http\Controllers;

use App\Models\AiAnalysis;
use App\Services\AiAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAnalysisController extends Controller
{
    public function __construct(
        protected AiAnalysisService $analysisService
    ) {
    }

    public function index(Request $request): View
    {
        AiAnalysis::query()
            ->where('status', 'pending')
            ->whereNull('response_text')
            ->whereNull('error_message')
            ->where('created_at', '<', now()->subMinutes(2))
            ->update([
                'status' => 'failed',
                'error_message' => 'Analiz islemi tamamlanmadi. Islem yarida kalmis olabilir.',
                'updated_at' => now(),
            ]);

        $query = AiAnalysis::with('user')->latest('id');

        if ($request->filled('type')) {
            $query->where('analysis_type', $request->string('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('sistem-ai-analizler.index', [
            'analyses' => $query->limit(100)->get(),
            'analysisTypes' => $this->analysisService->availableTypes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'analysis_type' => 'required|string|in:dashboard,ziyaret',
        ]);

        try {
            $analysis = $this->analysisService->create($validated['analysis_type'], $request->user());
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('system.ai-analyses.index', ['highlight' => $analysis->id])
            ->with('success', 'AI analizi olusturuldu.');
    }
}
