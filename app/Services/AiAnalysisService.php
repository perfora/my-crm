<?php

namespace App\Services;

use App\Models\AiAnalysis;
use App\Models\Musteri;
use App\Models\TumIsler;
use App\Models\User;
use App\Models\Ziyaret;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AiAnalysisService
{
    public function __construct(
        protected OpenAiService $openAiService
    ) {
    }

    public function create(string $type, ?User $user = null): AiAnalysis
    {
        $payload = $this->buildPayload($type);
        $config = $this->analysisConfig($type);

        $analysis = AiAnalysis::create([
            'title' => $config['title'],
            'analysis_type' => $type,
            'source_page' => $config['source_page'],
            'prompt_key' => $config['prompt_key'],
            'prompt_version' => $config['prompt_version'],
            'request_payload' => $payload,
            'status' => 'pending',
            'requested_by' => $user?->id,
        ]);

        try {
            $result = $this->openAiService->analyze($this->buildPrompt($type, $payload));

            $analysis->update([
                'status' => 'completed',
                'response_text' => $result['text'],
                'response_meta' => $result['meta'],
                'error_message' => null,
            ]);
        } catch (\Throwable $exception) {
            $analysis->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        return $analysis->fresh(['user']);
    }

    public function availableTypes(): array
    {
        return [
            'dashboard' => 'Dashboard Analizi',
            'ziyaret' => 'Ziyaret Analizi',
        ];
    }

    protected function buildPayload(string $type): array
    {
        return match ($type) {
            'dashboard' => $this->buildDashboardPayload(),
            'ziyaret' => $this->buildZiyaretPayload(),
            default => throw new RuntimeException('Bilinmeyen analiz tipi: '.$type),
        };
    }

    protected function buildPrompt(string $type, array $payload): string
    {
        return match ($type) {
            'dashboard' => $this->buildDashboardPrompt($payload),
            'ziyaret' => $this->buildZiyaretPrompt($payload),
            default => throw new RuntimeException('Prompt tanimi yok: '.$type),
        };
    }

    protected function analysisConfig(string $type): array
    {
        return match ($type) {
            'dashboard' => [
                'title' => 'Dashboard Analizi',
                'source_page' => 'dashboard',
                'prompt_key' => 'dashboard_v1',
                'prompt_version' => 1,
            ],
            'ziyaret' => [
                'title' => 'Ziyaret Analizi',
                'source_page' => 'ziyaretler',
                'prompt_key' => 'ziyaret_v1',
                'prompt_version' => 1,
            ],
            default => throw new RuntimeException('Analiz konfigrasyonu yok: '.$type),
        };
    }

    protected function buildDashboardPayload(): array
    {
        $currentYear = now()->year;
        $isler = TumIsler::query()->whereYear('created_at', $currentYear);

        $toplamTeklif = (clone $isler)->sum('teklif_tutari');
        $toplamAlis = (clone $isler)->sum('alis_tutari');

        return [
            'year' => $currentYear,
            'counts' => [
                'musteriler' => Musteri::count(),
                'isler' => (clone $isler)->count(),
                'ziyaretler' => Ziyaret::count(),
            ],
            'totals' => [
                'teklif' => round((float) $toplamTeklif, 2),
                'alis' => round((float) $toplamAlis, 2),
                'kar' => round((float) $toplamTeklif - (float) $toplamAlis, 2),
            ],
            'is_tipleri' => TumIsler::query()
                ->select('tipi', DB::raw('COUNT(*) as adet'))
                ->whereNotNull('tipi')
                ->groupBy('tipi')
                ->orderByDesc('adet')
                ->limit(10)
                ->get()
                ->map(fn ($row) => ['tipi' => $row->tipi, 'adet' => (int) $row->adet])
                ->all(),
        ];
    }

    protected function buildZiyaretPayload(): array
    {
        $statusCounts = Ziyaret::query()
            ->select('durumu', DB::raw('COUNT(*) as adet'))
            ->whereNotNull('durumu')
            ->groupBy('durumu')
            ->pluck('adet', 'durumu')
            ->map(fn ($value) => (int) $value)
            ->all();

        $typeCounts = Ziyaret::query()
            ->select('tur', DB::raw('COUNT(*) as adet'))
            ->whereNotNull('tur')
            ->groupBy('tur')
            ->pluck('adet', 'tur')
            ->map(fn ($value) => (int) $value)
            ->all();

        $recent = Ziyaret::with('musteri:id,sirket')
            ->orderByDesc('gerceklesen_tarih')
            ->orderByDesc('ziyaret_tarihi')
            ->limit(10)
            ->get()
            ->map(function (Ziyaret $ziyaret) {
                return [
                    'id' => $ziyaret->id,
                    'isim' => $ziyaret->ziyaret_ismi,
                    'musteri' => $ziyaret->musteri?->sirket,
                    'tur' => $ziyaret->tur,
                    'durumu' => $ziyaret->durumu,
                    'ziyaret_tarihi' => optional($ziyaret->ziyaret_tarihi)?->format('Y-m-d H:i'),
                    'gerceklesen_tarih' => optional($ziyaret->gerceklesen_tarih)?->format('Y-m-d H:i'),
                    'not_ozeti' => trim((string) str($ziyaret->ziyaret_notlari)->limit(140)),
                ];
            })
            ->all();

        return [
            'counts' => [
                'toplam' => Ziyaret::count(),
                'durumlar' => $statusCounts,
                'turler' => $typeCounts,
            ],
            'recent_items' => $recent,
        ];
    }

    protected function buildDashboardPrompt(array $payload): string
    {
        return <<<PROMPT
Sen bir CRM analiz asistanisin.

Asagidaki dashboard verisini Turkce olarak analiz et. Cevabi duz metin yaz. Markdown isaretleri kullanma.

Basliklar:
1. Genel Durum
2. Riskler
3. Firsatlar
4. Hemen Yapilacaklar

Veri:
Yil: {$payload['year']}
Musteri sayisi: {$payload['counts']['musteriler']}
Is sayisi: {$payload['counts']['isler']}
Ziyaret sayisi: {$payload['counts']['ziyaretler']}
Toplam teklif: {$payload['totals']['teklif']}
Toplam alis: {$payload['totals']['alis']}
Toplam kar: {$payload['totals']['kar']}

Tip bazli ilk 10 dagilim:
{$this->encodeForPrompt($payload['is_tipleri'])}
PROMPT;
    }

    protected function buildZiyaretPrompt(array $payload): string
    {
        return <<<PROMPT
Sen bir CRM ziyaret analiz asistanisin.

Asagidaki ziyaret verisini Turkce olarak analiz et. Cevabi duz metin yaz. Markdown kullanma.

Basliklar:
1. Genel Durum
2. Operasyonel Riskler
3. Firsatlar
4. Hemen Yapilacaklar

Toplam ziyaret kaydi:
{$payload['counts']['toplam']}

Durum dagilimi:
{$this->encodeForPrompt($payload['counts']['durumlar'])}

Tur dagilimi:
{$this->encodeForPrompt($payload['counts']['turler'])}

Son 10 kayit:
{$this->encodeForPrompt($payload['recent_items'])}
PROMPT;
    }

    protected function encodeForPrompt(array $payload): string
    {
        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }
}
