<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Kisi;
use App\Models\Ziyaret;
use App\Models\TumIsler;
use App\Models\SystemLog;
use App\Models\ChangeJournal;
use App\Support\LogSanitizer;
use App\Services\TcmbExchangeService;
use App\Http\Controllers\AiTokenController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Api\RaporController;
use App\Http\Controllers\Api\YenilemeController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\NotionSettingsController;
use App\Http\Controllers\DashboardWidgetSettingsController;
use App\Http\Controllers\MarkaController;
use App\Http\Controllers\MetaDataController;
use App\Http\Controllers\MusteriController;
use App\Http\Controllers\KisiController;
use App\Http\Controllers\ZiyaretController;
use App\Http\Controllers\TumIslerController;

if (!function_exists('crmToIstanbulCarbon')) {
    function crmToIstanbulCarbon($value): \Carbon\Carbon
    {
        if ($value instanceof \Carbon\Carbon) {
            // DB'de timezone'suz saklanan tarihleri "yerel saat" olarak yorumla.
            return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value->format('Y-m-d H:i:s'), 'Europe/Istanbul');
        }

        return \Carbon\Carbon::parse($value, 'Europe/Istanbul');
    }
}

if (!function_exists('crmAutoFillTcmKur')) {
    function crmAutoFillTcmKur(array $validated, ?TumIsler $existing = null): array
    {
        $finalTipi = $validated['tipi'] ?? ($existing?->tipi);
        $finalKapanis = $validated['kapanis_tarihi'] ?? ($existing?->kapanis_tarihi);
        $finalKur = array_key_exists('kur', $validated) ? $validated['kur'] : ($existing?->kur);

        $kurBos = $finalKur === null || $finalKur === '' || (is_numeric($finalKur) && (float) $finalKur <= 0);
        if ($finalTipi !== 'Kazanıldı' || empty($finalKapanis) || !$kurBos) {
            return $validated;
        }

        $tcmbRate = app(TcmbExchangeService::class)->getUsdSellingRateForDate($finalKapanis);
        if ($tcmbRate !== null) {
            $validated['kur'] = $tcmbRate;
        }

        return $validated;
    }
}

// Login/Logout Routes (no auth middleware)
Route::get('/finans', [PageController::class, 'finans'])->name('finans')->middleware('auth');

Route::get('/login', [SessionController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [SessionController::class, 'store'])->middleware('guest');
Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');

// Static asset compatibility routes for environments serving files under /public
Route::get('/favicon.ico', fn () => redirect('/public/favicon.ico', 301));
Route::get('/favicon.svg', fn () => redirect('/public/favicon.svg', 301));
Route::get('/apple-touch-icon.png', fn () => redirect('/public/apple-touch-icon.png', 301));

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    
    // Ana sayfa - Telefon algılama ile yönlendirme
    Route::get('/', [PageController::class, 'home'])->name('home');
    
    // Mobil Routes
    Route::prefix('mobile')->group(function () {
        Route::get('/', [MobileController::class, 'index'])->name('mobile.index');
        Route::get('/yeni-is', [MobileController::class, 'yeniIs'])->name('mobile.yeni-is');
        Route::get('/yeni-ziyaret', [MobileController::class, 'yeniZiyaret'])->name('mobile.yeni-ziyaret');
        Route::get('/planli-kayitlar', [MobileController::class, 'planliKayitlar'])->name('mobile.planli-kayitlar');
        Route::get('/hizli-kayit', [MobileController::class, 'hizliKayit'])->name('mobile.hizli-kayit');
        Route::post('/hizli-kayit', [MobileController::class, 'hizliKayitStore'])->name('mobile.hizli-kayit.store');
        Route::post('/ziyaretler/{id}/tamamla', [MobileController::class, 'planliKayitTamamla'])->name('mobile.planli-kayitlar.tamamla');
        Route::get('/raporlar', [MobileController::class, 'raporlar'])->name('mobile.raporlar');
    });
    
    // Dashboard - Özelleştirilebilir widget sistemi (alias)
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard.index');

    // System logs screen
    Route::get('/sistem-loglari', [SystemLogController::class, 'index'])->name('system-logs.index');

    // AI API token management
    Route::get('/sistem/ai-api', [AiTokenController::class, 'index'])->name('system.ai-api.index');
    Route::post('/sistem/ai-api/tokens', [AiTokenController::class, 'store'])->name('system.ai-api.store');
    Route::post('/sistem/ai-api/tokens/{id}/toggle', [AiTokenController::class, 'toggle'])->name('system.ai-api.toggle');

    // System export
    Route::get('/sistem/disa-aktar', [App\Http\Controllers\SystemExportController::class, 'index'])->name('system-export.index');
    Route::post('/sistem/disa-aktar', [App\Http\Controllers\SystemExportController::class, 'export'])->name('system-export.download');

    // Takvim
    Route::get('/takvim', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/takvim/sync', [App\Http\Controllers\CalendarController::class, 'sync'])->name('calendar.sync');
    Route::post('/takvim/cleanup', [App\Http\Controllers\CalendarController::class, 'cleanup'])->name('calendar.cleanup');
    Route::post('/takvim/push-crm', [App\Http\Controllers\CalendarController::class, 'pushCrm'])->name('calendar.push-crm');
    
    // Settings ve Profile routes
    require __DIR__ . '/settings.php';

// API: Filter Widget Data
Route::post('/api/filter-widget-data', function(Request $request) {
    $filterData = $request->input('filterData', []);
    
    $query = TumIsler::query();
    
    // Filtreleri uygula (tum-isler/index.blade.php'deki mantığın aynısı)
    if(isset($filterData['name'])) {
        $query->where('name', 'LIKE', '%' . $filterData['name'] . '%');
    }
    
    // Tipi filtresi önce (yıl filtresi buna bağlı)
    if(isset($filterData['tipi'])) {
        $query->where('tipi', $filterData['tipi']);
    }
    
    // Yıl Filtresi - Tipi bazlı
    if(isset($filterData['yil'])) {
        $tipi = isset($filterData['tipi']) ? $filterData['tipi'] : null;
        // Eğer tipi Kazanıldı veya Kaybedildi ise, yıl filtresini kapanis_tarihi'ne göre yap
        if(in_array($tipi, ['Kazanıldı', 'Kaybedildi'])) {
            $query->whereYear('kapanis_tarihi', $filterData['yil']);
        } else {
            // Diğer durumlarda açılış tarihine göre filtrele
            $query->whereYear('is_guncellenme_tarihi', $filterData['yil']);
        }
    }
    
    if(isset($filterData['turu'])) {
        $query->where('turu', $filterData['turu']);
    }
    
    if(isset($filterData['oncelik'])) {
        $query->where('oncelik', $filterData['oncelik']);
    }
    
    if(isset($filterData['register_durum'])) {
        $query->where('register_durum', $filterData['register_durum']);
    }
    
    if(isset($filterData['musteri_id'])) {
        $query->where('musteri_id', $filterData['musteri_id']);
    }
    
    if(isset($filterData['marka_id'])) {
        $query->where('marka_id', $filterData['marka_id']);
    }
    
    if(isset($filterData['teklif_min'])) {
        $query->where('teklif_tutari', '>=', $filterData['teklif_min']);
    }
    if(isset($filterData['teklif_max'])) {
        $query->where('teklif_tutari', '<=', $filterData['teklif_max']);
    }
    
    if(isset($filterData['alis_min'])) {
        $query->where('alis_tutari', '>=', $filterData['alis_min']);
    }
    if(isset($filterData['alis_max'])) {
        $query->where('alis_tutari', '<=', $filterData['alis_max']);
    }
    
    if(isset($filterData['acilis_start'])) {
        $query->whereDate('is_guncellenme_tarihi', '>=', $filterData['acilis_start']);
    }
    if(isset($filterData['acilis_end'])) {
        $query->whereDate('is_guncellenme_tarihi', '<=', $filterData['acilis_end']);
    }
    
    if(isset($filterData['kapanis_start'])) {
        $query->whereDate('kapanis_tarihi', '>=', $filterData['kapanis_start']);
    }
    if(isset($filterData['kapanis_end'])) {
        $query->whereDate('kapanis_tarihi', '<=', $filterData['kapanis_end']);
    }
    
    if(isset($filterData['lisans_start'])) {
        $query->whereDate('lisans_bitis', '>=', $filterData['lisans_start']);
    }
    if(isset($filterData['lisans_end'])) {
        $query->whereDate('lisans_bitis', '<=', $filterData['lisans_end']);
    }
    
    if(isset($filterData['updated_start'])) {
        $query->whereDate('updated_at', '>=', $filterData['updated_start']);
    }
    if(isset($filterData['updated_end'])) {
        $query->whereDate('updated_at', '<=', $filterData['updated_end']);
    }
    
    $isler = $query->get();
    
    // Kar hesabını sonra yap (eğer kar filtresi varsa)
    if(isset($filterData['kar_min']) || isset($filterData['kar_max'])) {
        $isler = $isler->filter(function($is) use ($filterData) {
            $kar = ($is->teklif_tutari ?? 0) - ($is->alis_tutari ?? 0);
            $minOk = !isset($filterData['kar_min']) || $kar >= $filterData['kar_min'];
            $maxOk = !isset($filterData['kar_max']) || $kar <= $filterData['kar_max'];
            return $minOk && $maxOk;
        });
    }
    
    // Toplamları hesapla
    $totalTeklif = 0;
    $totalAlis = 0;
    
    foreach($isler as $is) {
        if($is->teklif_doviz === 'USD' && $is->teklif_tutari) {
            $totalTeklif += $is->teklif_tutari;
        }
        if($is->alis_doviz === 'USD' && $is->alis_tutari) {
            $totalAlis += $is->alis_tutari;
        }
    }
    
    $totalKar = $totalTeklif - $totalAlis;
    
    return response()->json([
        'count' => $isler->count(),
        'totalTeklif' => number_format($totalTeklif, 2),
        'totalKar' => number_format($totalKar, 2),
    ]);
});

// API: Yenileme
Route::post('/api/yenileme-ac', [YenilemeController::class, 'ac']);
Route::post('/api/yenileme-isaretle', [YenilemeController::class, 'isaretle']);

// API: Raporlar
Route::post('/api/rapor-marka', [RaporController::class, 'marka']);
Route::post('/api/rapor-musteri', [RaporController::class, 'musteri']);

// Notion Ayarları
Route::get('/notion-settings', [NotionSettingsController::class, 'index']);
Route::post('/notion-settings/update', [NotionSettingsController::class, 'update']);
Route::post('/notion-settings/sync', [NotionSettingsController::class, 'sync']);
Route::post('/notion-settings/push', [NotionSettingsController::class, 'push']);

// Widget Ayarları
Route::get('/dashboard/widget-settings', [DashboardWidgetSettingsController::class, 'index']);
Route::post('/dashboard/widget-settings', [DashboardWidgetSettingsController::class, 'update']);

// Markalar routes
Route::get('/markalar', [MarkaController::class, 'index']);
Route::get('/markalar/{id}', [MarkaController::class, 'show']);
Route::post('/markalar', [MarkaController::class, 'store']);
Route::get('/markalar/{id}/edit', [MarkaController::class, 'edit']);
Route::put('/markalar/{id}', [MarkaController::class, 'update']);
Route::delete('/markalar/{id}', [MarkaController::class, 'destroy']);

// İş Tipleri, Türleri ve Öncelik Routes (AJAX inline creation için)
Route::post('/is-tipleri', [MetaDataController::class, 'storeIsTipi']);
Route::post('/is-turleri', [MetaDataController::class, 'storeIsTuru']);
Route::post('/oncelikler', [MetaDataController::class, 'storeOncelik']);

// Müşteriler (Firmalar) routes
Route::get('/musteriler', [MusteriController::class, 'index']);
Route::get('/raporlar', [MusteriController::class, 'raporlar']);
Route::get('/musteriler/import', [MusteriController::class, 'import']);
Route::post('/musteriler', [MusteriController::class, 'store']);
Route::post('/musteriler/{id}/quick-contact', [MusteriController::class, 'quickContact']);
Route::post('/ziyaretler/{id}/quick-note', [MusteriController::class, 'quickNote']);
Route::get('/musteriler/{id}', [MusteriController::class, 'show']);
Route::get('/musteriler/{id}/edit', [MusteriController::class, 'edit']);
Route::put('/musteriler/{id}', [MusteriController::class, 'update']);
Route::delete('/musteriler/{id}', [MusteriController::class, 'destroy']);
Route::post('/musteriler/delete-turu', [MusteriController::class, 'deleteTuru']);

// Kişiler routes
Route::get('/kisiler', [KisiController::class, 'index']);
Route::post('/kisiler', [KisiController::class, 'store']);
Route::get('/kisiler/{id}/edit', [KisiController::class, 'edit']);
Route::put('/kisiler/{id}', [KisiController::class, 'update']);
Route::delete('/kisiler/{id}', [KisiController::class, 'destroy']);

// Tedarikçi Fiyatları
Route::get('/tedarikci-fiyatlari', [App\Http\Controllers\TedarikiciFiyatController::class, 'index']);
Route::post('/tedarikci-fiyatlari/bulk', [App\Http\Controllers\TedarikiciFiyatController::class, 'bulkStore']);
Route::delete('/tedarikci-fiyatlari/{id}', [App\Http\Controllers\TedarikiciFiyatController::class, 'destroy']);

// Ürünler
Route::get('/urunler', [App\Http\Controllers\UrunController::class, 'index']);
Route::post('/urunler', [App\Http\Controllers\UrunController::class, 'store']);
Route::put('/urunler/{id}', [App\Http\Controllers\UrunController::class, 'update']);
Route::delete('/urunler/{id}', [App\Http\Controllers\UrunController::class, 'destroy']);

// Fiyat Teklifleri
Route::get('/fiyat-teklifleri', [App\Http\Controllers\FiyatTeklifController::class, 'index']);
Route::get('/fiyat-teklifleri/yeni', [App\Http\Controllers\FiyatTeklifController::class, 'create']);
Route::post('/fiyat-teklifleri', [App\Http\Controllers\FiyatTeklifController::class, 'store']);
Route::get('/fiyat-teklifleri/{id}', [App\Http\Controllers\FiyatTeklifController::class, 'show']);
Route::delete('/fiyat-teklifleri/{id}', [App\Http\Controllers\FiyatTeklifController::class, 'destroy']);
Route::get('/api/musteriler/{id}/yetkililer', [App\Http\Controllers\FiyatTeklifController::class, 'getYetkililer']);

// Teklif Koşulları Yönetimi
Route::get('/teklif-kosullari', [App\Http\Controllers\TeklifKosuluController::class, 'index']);
Route::post('/teklif-kosullari', [App\Http\Controllers\TeklifKosuluController::class, 'store']);
Route::get('/teklif-kosullari/{id}/edit', [App\Http\Controllers\TeklifKosuluController::class, 'edit']);
Route::put('/teklif-kosullari/{id}', [App\Http\Controllers\TeklifKosuluController::class, 'update']);
Route::delete('/teklif-kosullari/{id}', [App\Http\Controllers\TeklifKosuluController::class, 'destroy']);
Route::post('/teklif-kosullari/{id}/varsayilan', [App\Http\Controllers\TeklifKosuluController::class, 'varsayilanYap']);
Route::get('/api/teklif-kosullari', [App\Http\Controllers\TeklifKosuluController::class, 'apiList']);

    // Ziyaretler routes
Route::get('/ziyaretler', [ZiyaretController::class, 'index']);
Route::get('/ziyaretler/{id}/edit', [ZiyaretController::class, 'edit']);
Route::put('/ziyaretler/{id}', [ZiyaretController::class, 'update']);
Route::delete('/ziyaretler/{id}', [ZiyaretController::class, 'destroy']);
Route::post('/ziyaretler', [ZiyaretController::class, 'store']);


    // Tüm İşler routes
Route::get('/tum-isler', [TumIslerController::class, 'index']);
Route::post('/tum-isler', [TumIslerController::class, 'store']);
Route::get('/tum-isler/{id}/edit', [TumIslerController::class, 'edit']);
Route::get('/tum-isler/{id}/duplicate', [TumIslerController::class, 'duplicate']);
Route::put('/tum-isler/{id}', [TumIslerController::class, 'update']);
Route::delete('/tum-isler/{id}', [TumIslerController::class, 'destroy']);

}); // End of auth middleware group

// Saved Filters API (auth middleware içinde OLMALI - sadece giriş yapmış kullanıcılar)
Route::middleware(['auth'])->group(function () {
    Route::get('/api/saved-filters', function () {
        $page = request('page', 'tum-isler');
        return \App\Models\SavedFilter::where('page', $page)->get();
    });

    Route::post('/api/saved-filters', function () {
        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'page' => 'required|string',
            'filter_data' => 'required|array',
        ]);
        
        $filter = \App\Models\SavedFilter::create($validated);
        return response()->json($filter);
    });

    Route::delete('/api/saved-filters/{name}', function ($name) {
        $page = request('page', 'tum-isler');
        \App\Models\SavedFilter::where('page', $page)->where('name', $name)->delete();
        return response()->json(['success' => true]);
    });

    // Client-side (F12) errors
    Route::post('/api/client-errors', function (Request $request) {
        $data = $request->validate([
            'level' => 'nullable|string|max:32',
            'source' => 'nullable|string|max:128',
            'message' => 'required|string|max:4000',
            'file' => 'nullable|string|max:1000',
            'line' => 'nullable|integer',
            'col' => 'nullable|integer',
            'stack' => 'nullable|string|max:12000',
            'url' => 'nullable|string|max:2000',
            'user_agent' => 'nullable|string|max:4000',
        ]);

        $fingerprint = hash(
            'sha256',
            ($data['message'] ?? '').'|'.($data['file'] ?? '').'|'.($data['line'] ?? '').'|'.($data['url'] ?? '')
        );

        SystemLog::create([
            'channel' => 'client',
            'level' => $data['level'] ?? 'error',
            'source' => $data['source'] ?? 'js',
            'message' => $data['message'],
            'file' => $data['file'] ?? null,
            'line' => $data['line'] ?? null,
            'url' => $data['url'] ?? $request->headers->get('referer'),
            'method' => 'CLIENT',
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $data['user_agent'] ?? $request->userAgent(),
            'request_id' => (string) Str::uuid(),
            'fingerprint' => $fingerprint,
            'context' => LogSanitizer::sanitize([
                'col' => $data['col'] ?? null,
                'stack' => $data['stack'] ?? null,
            ]),
        ]);

        return response()->json(['ok' => true]);
    })->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

    // Change journal: attempt-based tracking for AI/manual iterations
    Route::get('/api/change-journals', function (Request $request) {
        $query = ChangeJournal::query()->latest('id');
        if ($request->filled('task_key')) {
            $query->where('task_key', $request->string('task_key'));
        }
        return response()->json($query->limit(100)->get());
    });

    Route::post('/api/change-journals', function (Request $request) {
        $data = $request->validate([
            'task_key' => 'nullable|string|max:128',
            'attempt_no' => 'nullable|integer|min:1',
            'actor' => 'required|string|max:64',
            'status' => 'required|in:pending,success,fail',
            'summary' => 'required|string|max:4000',
            'commit_hash' => 'nullable|string|max:64',
            'meta' => 'nullable|array',
        ]);

        $journal = ChangeJournal::create([
            'task_key' => $data['task_key'] ?? null,
            'attempt_no' => $data['attempt_no'] ?? 1,
            'actor' => $data['actor'],
            'status' => $data['status'],
            'summary' => $data['summary'],
            'commit_hash' => $data['commit_hash'] ?? null,
            'user_id' => auth()->id(),
            'meta' => LogSanitizer::sanitize($data['meta'] ?? []),
        ]);

        return response()->json($journal);
    });
});
