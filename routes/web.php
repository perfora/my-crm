<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\TumIsler;
use App\Services\TcmbExchangeService;
use App\Http\Controllers\AiTokenController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Api\RaporController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\WidgetDataController;
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
Route::post('/api/filter-widget-data', [WidgetDataController::class, 'filter']);

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
    Route::get('/api/saved-filters', [TrackingController::class, 'savedFiltersIndex']);
    Route::post('/api/saved-filters', [TrackingController::class, 'savedFiltersStore']);
    Route::delete('/api/saved-filters/{name}', [TrackingController::class, 'savedFiltersDestroy']);

    // Client-side (F12) errors
    Route::post('/api/client-errors', [TrackingController::class, 'clientErrors'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

    // Change journal: attempt-based tracking for AI/manual iterations
    Route::get('/api/change-journals', [TrackingController::class, 'changeJournalsIndex']);
    Route::post('/api/change-journals', [TrackingController::class, 'changeJournalsStore']);
});
