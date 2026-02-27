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
Route::get('/kisiler', fn () => view('kisiler.index'));
Route::get('/kisiler/{id}/edit', function ($id) {
    $kisi = \App\Models\Kisi::findOrFail($id);
    return view('kisiler.edit', compact('kisi'));
});
Route::put('/kisiler/{id}', function ($id) {
    $kisi = \App\Models\Kisi::findOrFail($id);
    
    // AJAX inline editing için
    if (request()->ajax() && request()->has(request()->keys()[1])) {
        $field = request()->keys()[1]; // _token sonraki field
        $value = request()->input($field);
        
        $kisi->update([$field => $value]);
        
        return response()->json([
            'success' => true,
            'message' => 'Güncellendi',
            'data' => $kisi
        ]);
    }
    
    // Normal form submit için
    $validated = request()->validate([
        'ad_soyad' => 'required|max:255',
        'telefon_numarasi' => 'nullable|string',
        'email_adresi' => 'nullable|email',
        'bolum' => 'nullable|string',
        'gorev' => 'nullable|string',
        'musteri_id' => 'nullable|exists:musteriler,id',
        'url' => 'nullable|url',
    ]);
    
    $kisi->update($validated);
    
    return redirect('/kisiler')->with('message', 'Kişi güncellendi.');
});
Route::delete('/kisiler/{id}', function ($id) {
    $kisi = \App\Models\Kisi::findOrFail($id);
    $kisi->delete();
    
    if (request()->ajax()) {
        return response()->json(['success' => true, 'message' => 'Kişi silindi.']);
    }
    
    return redirect('/kisiler')->with('message', 'Kişi silindi.');
});

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

Route::post('/kisiler', function () {
    $validated = request()->validate([
        'ad_soyad' => 'required|max:255',
        'telefon_numarasi' => 'nullable|string',
        'email_adresi' => 'nullable|email',
        'bolum' => 'nullable|string',
        'gorev' => 'nullable|string',
        'musteri_id' => 'nullable|exists:musteriler,id',
        'url' => 'nullable|url',
    ]);
    
    \App\Models\Kisi::create($validated);
    
    return redirect('/kisiler')->with('message', 'Kişi başarıyla eklendi.');
});


    // Ziyaretler routes
Route::get('/ziyaretler', fn () => view('ziyaretler.index'));
Route::get('/ziyaretler/{id}/edit', function ($id) {
    $ziyaret = \App\Models\Ziyaret::findOrFail($id);
    return view('ziyaretler.edit', compact('ziyaret'));
});
Route::put('/ziyaretler/{id}', function ($id) {
    $ziyaret = \App\Models\Ziyaret::findOrFail($id);
    
    $validated = request()->validate([
        'ziyaret_ismi' => 'sometimes|nullable|max:255',
        'musteri_id' => 'sometimes|nullable|exists:musteriler,id',
        'ziyaret_tarihi' => 'sometimes|nullable|date',
        'arama_tarihi' => 'sometimes|nullable|date',
        'tur' => 'sometimes|nullable|string',
        'durumu' => 'sometimes|nullable|string',
        'ziyaret_notlari' => 'sometimes|nullable|string',
    ]);
    
    if (!empty($validated['ziyaret_tarihi']) && empty($validated['durumu'])) {
        $validated['durumu'] = 'Planlandı';
    }
    if (!empty($validated['arama_tarihi']) && empty($validated['durumu'])) {
        $validated['durumu'] = 'Planlandı';
    }
    if (($validated['durumu'] ?? null) === 'Tamamlandı') {
        $validated['gerceklesen_tarih'] = $ziyaret->gerceklesen_tarih ?? \Carbon\Carbon::now('Europe/Istanbul');
    }

    $ziyaret->update($validated);

    // Outlook senkron - Beklemede/Planlandı ise yaz
    if (in_array($ziyaret->durumu, ['Beklemede', 'Planlandı']) && $ziyaret->ziyaret_tarihi) {
        $subject = $ziyaret->ziyaret_ismi ?: 'Ziyaret';
        $start = crmToIstanbulCarbon($ziyaret->ziyaret_tarihi);
        $end = $start->copy()->addMinutes(30);
        $body = $ziyaret->ziyaret_notlari ?? '';
        $ews = app(\App\Services\ExchangeEwsService::class);
        $result = $ews->createOrUpdateVisitEvent(
            $ziyaret->ews_item_id,
            $ziyaret->ews_change_key,
            $subject,
            $start,
            $end,
            $body
        );
        if (empty($result['error']) && !empty($result['item_id'])) {
            $ziyaret->update([
                'ews_item_id' => $result['item_id'],
                'ews_change_key' => $result['change_key'] ?? $ziyaret->ews_change_key,
            ]);
        }
    }
    
    return redirect('/ziyaretler')->with('message', 'Ziyaret güncellendi.');
});
Route::delete('/ziyaretler/{id}', function ($id) {
    $ziyaret = \App\Models\Ziyaret::findOrFail($id);
    if ($ziyaret->ews_item_id) {
        try {
            $ews = app(\App\Services\ExchangeEwsService::class);
            $ews->deleteVisitEvent($ziyaret->ews_item_id, $ziyaret->ews_change_key);
        } catch (\Throwable $e) {
            \Log::warning('EWS delete failed for ziyaret', [
                'id' => $ziyaret->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
    $ziyaret->delete();
    
    return redirect('/ziyaretler')->with('message', 'Ziyaret silindi.');
});
Route::post('/ziyaretler', function () {
    $validated = request()->validate([
        'ziyaret_ismi' => 'nullable|max:255',
        'musteri_id' => 'nullable|exists:musteriler,id',
        'ziyaret_tarihi' => 'nullable|date',
        'arama_tarihi' => 'nullable|date',
        'tur' => 'nullable|string',
        'durumu' => 'nullable|string',
        'ziyaret_notlari' => 'nullable|string',
        'notlar' => 'nullable|string', // Mobil form için
    ]);
    
    // Mobil formdan geliyorsa notlar alanını ziyaret_notlari olarak kaydet
    if (isset($validated['notlar'])) {
        $validated['ziyaret_notlari'] = $validated['notlar'];
        unset($validated['notlar']);
    }
    
    // Ziyaret ismi yoksa müşteri adını kullan
    if (empty($validated['ziyaret_ismi']) && !empty($validated['musteri_id'])) {
        $musteri = \App\Models\Musteri::find($validated['musteri_id']);
        $validated['ziyaret_ismi'] = $musteri ? $musteri->sirket . ' Ziyareti' : 'Ziyaret';
    }
    
    if (!empty($validated['ziyaret_tarihi']) && empty($validated['durumu'])) {
        $validated['durumu'] = 'Planlandı';
    }
    if (!empty($validated['arama_tarihi']) && empty($validated['durumu'])) {
        $validated['durumu'] = 'Planlandı';
    }
    if (($validated['durumu'] ?? null) === 'Tamamlandı' && empty($validated['gerceklesen_tarih'])) {
        $validated['gerceklesen_tarih'] = \Carbon\Carbon::now('Europe/Istanbul');
    }
    
    $ziyaret = \App\Models\Ziyaret::create($validated);

    if (request()->ajax()) {
        $musteri = null;
        if (!empty($ziyaret->musteri_id)) {
            $musteri = \App\Models\Musteri::find($ziyaret->musteri_id);
        }
        return response()->json([
            'id' => $ziyaret->id,
            'musteri' => $musteri ? ['id' => $musteri->id, 'sirket' => $musteri->sirket] : null,
        ]);
    }

    // Outlook senkron - Beklemede/Planlandı ise yaz
    if (in_array($ziyaret->durumu, ['Beklemede', 'Planlandı']) && $ziyaret->ziyaret_tarihi) {
        $subject = $ziyaret->ziyaret_ismi ?: 'Ziyaret';
        $start = crmToIstanbulCarbon($ziyaret->ziyaret_tarihi);
        $end = $start->copy()->addMinutes(30);
        $body = $ziyaret->ziyaret_notlari ?? '';
        $ews = app(\App\Services\ExchangeEwsService::class);
        $result = $ews->createOrUpdateVisitEvent(
            null,
            null,
            $subject,
            $start,
            $end,
            $body
        );
        if (empty($result['error']) && !empty($result['item_id'])) {
            $ziyaret->update([
                'ews_item_id' => $result['item_id'],
                'ews_change_key' => $result['change_key'] ?? null,
            ]);
        }
    }
    
    // Mobil'den geliyorsa mobil'e yönlendir
    if (str_contains(request()->header('referer', ''), '/mobile')) {
        return redirect('/mobile')->with('message', 'Ziyaret başarıyla eklendi.');
    }
    
    return redirect('/ziyaretler')->with('message', 'Ziyaret başarıyla eklendi.');
});


    // Tüm İşler routes
Route::get('/tum-isler', fn () => view('tum-isler.index'));
Route::post('/tum-isler', function () {
    $validated = request()->validate([
        'name' => 'nullable|max:255',
        'musteri_id' => 'nullable|exists:musteriler,id',
        'marka_id' => 'nullable|exists:markalar,id',
        'tipi' => 'nullable|string',
        'turu' => 'nullable|string',
        'oncelik' => 'nullable|string',
        'kaybedilme_nedeni' => 'nullable|string',
        'register_durum' => 'nullable|string',
        'teklif_tutari' => 'nullable|numeric',
        'teklif_doviz' => 'nullable|string',
        'alis_tutari' => 'nullable|numeric',
        'alis_doviz' => 'nullable|string',
        'kur' => 'nullable|numeric',
        'kapanis_tarihi' => 'nullable|date',
        'lisans_bitis' => 'nullable|date',
        'is_guncellenme_tarihi' => 'nullable|date',
        'notlar' => 'nullable|string',
        'gecmis_notlar' => 'nullable|string',
        'aciklama' => 'nullable|string',
    ]);

    // Varsayılanlar: kullanıcı seçmezse
    if (empty($validated['tipi'])) {
        $validated['tipi'] = 'Verilecek';
    }
    if (empty($validated['oncelik'])) {
        $validated['oncelik'] = '1';
    }

    // Varsayılan döviz: kullanıcı seçmediyse ve tutar girildiyse USD kabul et
    if (!empty($validated['teklif_tutari']) && empty($validated['teklif_doviz'])) {
        $validated['teklif_doviz'] = 'USD';
    }
    if (!empty($validated['alis_tutari']) && empty($validated['alis_doviz'])) {
        $validated['alis_doviz'] = 'USD';
    }
    $validated = crmAutoFillTcmKur($validated);
    
    // AJAX inline editing için yeni kayıt
    if (request()->ajax() || request()->wantsJson()) {
        $is = \App\Models\TumIsler::create(array_merge($validated, [
            'is_guncellenme_tarihi' => $validated['is_guncellenme_tarihi'] ?? now()
        ]));
        $is->load(['musteri', 'marka']);
        return response()->json(['success' => true, 'data' => $is]);
    }
    
    \App\Models\TumIsler::create(array_merge($validated, [
        'is_guncellenme_tarihi' => $validated['is_guncellenme_tarihi'] ?? now()
    ]));
    
    // Mobil'den geliyorsa mobil'e yönlendir
    if (str_contains(request()->header('referer', ''), '/mobile')) {
        return redirect('/mobile')->with('message', 'İş başarıyla eklendi.');
    }
    
    return redirect('/tum-isler')->with('message', 'İş başarıyla eklendi.');
});

// Tüm İşler routes - Edit/Delete/Duplicate
Route::get('/tum-isler/{id}/edit', function ($id) {
    $is = \App\Models\TumIsler::findOrFail($id);
    return view('tum-isler.edit', compact('is'));
});
Route::get('/tum-isler/{id}/duplicate', function ($id) {
    $is = \App\Models\TumIsler::findOrFail($id);
    
    // Yeni kayıt oluştur
    $newIs = $is->replicate();
    $newIs->name = $is->name . ' (Kopya)';
    $newIs->is_guncellenme_tarihi = now();
    $newIs->kapanis_tarihi = null;
    $newIs->save();
    
    return redirect('/tum-isler/' . $newIs->id . '/edit')->with('message', 'İş kopyalandı. Düzenleyebilirsiniz.');
});
Route::put('/tum-isler/{id}', function ($id) {
    $is = \App\Models\TumIsler::findOrFail($id);
    
    // AJAX inline editing için
    if (request()->ajax() || request()->wantsJson()) {
        $validated = request()->validate([
            'name' => 'sometimes|required|max:255',
            'musteri_id' => 'nullable|exists:musteriler,id',
            'marka_id' => 'nullable|exists:markalar,id',
            'tipi' => 'nullable|string',
            'durum' => 'nullable|string',
            'turu' => 'nullable|string',
            'oncelik' => 'nullable|string',
            'kaybedilme_nedeni' => 'nullable|string',
            'register_durum' => 'nullable|string',
            'teklif_tutari' => 'nullable|numeric',
            'teklif_doviz' => 'nullable|string',
            'alis_tutari' => 'nullable|numeric',
            'alis_doviz' => 'nullable|string',
            'kur' => 'nullable|numeric',
            'kapanis_tarihi' => 'nullable|date',
            'lisans_bitis' => 'nullable|date',
            'is_guncellenme_tarihi' => 'nullable|date',
            'notlar' => 'nullable|string',
            'gecmis_notlar' => 'nullable|string',
            'aciklama' => 'nullable|string',
        ]);

        // Inline düzenlemede döviz seçilmediyse, girilen tutarı USD say
        if (array_key_exists('teklif_tutari', $validated) && !empty($validated['teklif_tutari']) && empty($validated['teklif_doviz'])) {
            $validated['teklif_doviz'] = 'USD';
        }
        if (array_key_exists('alis_tutari', $validated) && !empty($validated['alis_tutari']) && empty($validated['alis_doviz'])) {
            $validated['alis_doviz'] = 'USD';
        }
        $validated = crmAutoFillTcmKur($validated, $is);
        
        $is->update($validated);
        
        // Reload relationships for response
        $is->load(['musteri', 'marka']);
        
        return response()->json([
            'success' => true,
            'message' => 'Güncellendi',
            'data' => $is
        ]);
    }
    
    // Normal form submit için
    $validated = request()->validate([
        'name' => 'required|max:255',
        'musteri_id' => 'nullable|exists:musteriler,id',
        'marka_id' => 'nullable|exists:markalar,id',
        'tipi' => 'nullable|string',
        'turu' => 'nullable|string',
        'oncelik' => 'nullable|string',
        'kaybedilme_nedeni' => 'nullable|string',
        'register_durum' => 'nullable|string',
        'teklif_tutari' => 'nullable|numeric',
        'teklif_doviz' => 'nullable|string',
        'alis_tutari' => 'nullable|numeric',
        'alis_doviz' => 'nullable|string',
        'kur' => 'nullable|numeric',
        'kapanis_tarihi' => 'nullable|date',
        'lisans_bitis' => 'nullable|date',
        'is_guncellenme_tarihi' => 'nullable|date',
        'notlar' => 'nullable|string',
        'gecmis_notlar' => 'nullable|string',
        'aciklama' => 'nullable|string',
    ]);

    if (!empty($validated['teklif_tutari']) && empty($validated['teklif_doviz'])) {
        $validated['teklif_doviz'] = 'USD';
    }
    if (!empty($validated['alis_tutari']) && empty($validated['alis_doviz'])) {
        $validated['alis_doviz'] = 'USD';
    }
    $validated = crmAutoFillTcmKur($validated, $is);
    
    $is->update($validated);
    
    return redirect('/tum-isler')->with('message', 'İş güncellendi.');
});
Route::delete('/tum-isler/{id}', function ($id) {
    $is = \App\Models\TumIsler::findOrFail($id);
    $is->delete();
    
    // AJAX bulk delete için
    if (request()->ajax() || request()->wantsJson()) {
        return response()->json(['success' => true, 'message' => 'İş silindi.']);
    }
    
    return redirect('/tum-isler')->with('message', 'İş silindi.');
});

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
