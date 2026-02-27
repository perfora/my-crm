<?php

Route::middleware(['auth'])->group(function () {
    Route::get('/', [\App\Http\Controllers\PageController::class, 'home'])->name('home');

    Route::prefix('mobile')->group(function () {
        Route::get('/', [\App\Http\Controllers\MobileController::class, 'index'])->name('mobile.index');
        Route::get('/yeni-is', [\App\Http\Controllers\MobileController::class, 'yeniIs'])->name('mobile.yeni-is');
        Route::get('/yeni-ziyaret', [\App\Http\Controllers\MobileController::class, 'yeniZiyaret'])->name('mobile.yeni-ziyaret');
        Route::get('/planli-kayitlar', [\App\Http\Controllers\MobileController::class, 'planliKayitlar'])->name('mobile.planli-kayitlar');
        Route::get('/hizli-kayit', [\App\Http\Controllers\MobileController::class, 'hizliKayit'])->name('mobile.hizli-kayit');
        Route::post('/hizli-kayit', [\App\Http\Controllers\MobileController::class, 'hizliKayitStore'])->name('mobile.hizli-kayit.store');
        Route::post('/ziyaretler/{id}/tamamla', [\App\Http\Controllers\MobileController::class, 'planliKayitTamamla'])->name('mobile.planli-kayitlar.tamamla');
        Route::get('/raporlar', [\App\Http\Controllers\MobileController::class, 'raporlar'])->name('mobile.raporlar');
    });

    Route::get('/dashboard', [\App\Http\Controllers\PageController::class, 'dashboard'])->name('dashboard.index');
    Route::get('/sistem-loglari', [\App\Http\Controllers\SystemLogController::class, 'index'])->name('system-logs.index');

    Route::get('/sistem/ai-api', [\App\Http\Controllers\AiTokenController::class, 'index'])->name('system.ai-api.index');
    Route::post('/sistem/ai-api/tokens', [\App\Http\Controllers\AiTokenController::class, 'store'])->name('system.ai-api.store');
    Route::post('/sistem/ai-api/tokens/{id}/toggle', [\App\Http\Controllers\AiTokenController::class, 'toggle'])->name('system.ai-api.toggle');

    Route::get('/sistem/disa-aktar', [\App\Http\Controllers\SystemExportController::class, 'index'])->name('system-export.index');
    Route::post('/sistem/disa-aktar', [\App\Http\Controllers\SystemExportController::class, 'export'])->name('system-export.download');

    Route::get('/takvim', [\App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/takvim/sync', [\App\Http\Controllers\CalendarController::class, 'sync'])->name('calendar.sync');
    Route::post('/takvim/cleanup', [\App\Http\Controllers\CalendarController::class, 'cleanup'])->name('calendar.cleanup');
    Route::post('/takvim/push-crm', [\App\Http\Controllers\CalendarController::class, 'pushCrm'])->name('calendar.push-crm');

    require base_path('routes/settings.php');

    Route::post('/api/filter-widget-data', [\App\Http\Controllers\Api\WidgetDataController::class, 'filter'])
        ->name('api.filter-widget-data');

    Route::post('/api/yenileme-ac', [\App\Http\Controllers\Api\YenilemeController::class, 'ac']);
    Route::post('/api/yenileme-isaretle', [\App\Http\Controllers\Api\YenilemeController::class, 'isaretle']);

    Route::post('/api/rapor-marka', [\App\Http\Controllers\Api\RaporController::class, 'marka'])->name('api.rapor.marka');
    Route::post('/api/rapor-musteri', [\App\Http\Controllers\Api\RaporController::class, 'musteri'])->name('api.rapor.musteri');

    Route::get('/notion-settings', [\App\Http\Controllers\NotionSettingsController::class, 'index'])->name('notion-settings.index');
    Route::post('/notion-settings/update', [\App\Http\Controllers\NotionSettingsController::class, 'update'])->name('notion-settings.update');
    Route::post('/notion-settings/sync', [\App\Http\Controllers\NotionSettingsController::class, 'sync'])->name('notion-settings.sync');
    Route::post('/notion-settings/push', [\App\Http\Controllers\NotionSettingsController::class, 'push'])->name('notion-settings.push');

    Route::get('/dashboard/widget-settings', [\App\Http\Controllers\DashboardWidgetSettingsController::class, 'index'])
        ->name('dashboard.widget-settings.index');
    Route::post('/dashboard/widget-settings', [\App\Http\Controllers\DashboardWidgetSettingsController::class, 'update'])
        ->name('dashboard.widget-settings.update');

    Route::get('/markalar', [\App\Http\Controllers\MarkaController::class, 'index']);
    Route::get('/markalar/{id}', [\App\Http\Controllers\MarkaController::class, 'show']);
    Route::post('/markalar', [\App\Http\Controllers\MarkaController::class, 'store']);
    Route::get('/markalar/{id}/edit', [\App\Http\Controllers\MarkaController::class, 'edit']);
    Route::put('/markalar/{id}', [\App\Http\Controllers\MarkaController::class, 'update']);
    Route::delete('/markalar/{id}', [\App\Http\Controllers\MarkaController::class, 'destroy']);

    Route::post('/is-tipleri', [\App\Http\Controllers\MetaDataController::class, 'storeIsTipi']);
    Route::post('/is-turleri', [\App\Http\Controllers\MetaDataController::class, 'storeIsTuru']);
    Route::post('/oncelikler', [\App\Http\Controllers\MetaDataController::class, 'storeOncelik']);

    Route::get('/musteriler', [\App\Http\Controllers\MusteriController::class, 'index']);
    Route::get('/raporlar', [\App\Http\Controllers\MusteriController::class, 'raporlar']);
    Route::get('/musteriler/import', [\App\Http\Controllers\MusteriController::class, 'import']);
    Route::post('/musteriler', [\App\Http\Controllers\MusteriController::class, 'store']);
    Route::post('/musteriler/{id}/quick-contact', [\App\Http\Controllers\MusteriController::class, 'quickContact']);
    Route::post('/ziyaretler/{id}/quick-note', [\App\Http\Controllers\MusteriController::class, 'quickNote']);
    Route::get('/musteriler/{id}', [\App\Http\Controllers\MusteriController::class, 'show']);
    Route::get('/musteriler/{id}/edit', [\App\Http\Controllers\MusteriController::class, 'edit']);
    Route::put('/musteriler/{id}', [\App\Http\Controllers\MusteriController::class, 'update']);
    Route::delete('/musteriler/{id}', [\App\Http\Controllers\MusteriController::class, 'destroy']);
    Route::post('/musteriler/delete-turu', [\App\Http\Controllers\MusteriController::class, 'deleteTuru']);

    Route::get('/kisiler', [\App\Http\Controllers\KisiController::class, 'index']);
    Route::post('/kisiler', [\App\Http\Controllers\KisiController::class, 'store']);
    Route::get('/kisiler/{id}/edit', [\App\Http\Controllers\KisiController::class, 'edit']);
    Route::put('/kisiler/{id}', [\App\Http\Controllers\KisiController::class, 'update']);
    Route::delete('/kisiler/{id}', [\App\Http\Controllers\KisiController::class, 'destroy']);

    Route::get('/tedarikci-fiyatlari', [\App\Http\Controllers\TedarikiciFiyatController::class, 'index']);
    Route::post('/tedarikci-fiyatlari/bulk', [\App\Http\Controllers\TedarikiciFiyatController::class, 'bulkStore']);
    Route::delete('/tedarikci-fiyatlari/{id}', [\App\Http\Controllers\TedarikiciFiyatController::class, 'destroy']);

    Route::get('/urunler', [\App\Http\Controllers\UrunController::class, 'index']);
    Route::post('/urunler', [\App\Http\Controllers\UrunController::class, 'store']);
    Route::put('/urunler/{id}', [\App\Http\Controllers\UrunController::class, 'update']);
    Route::delete('/urunler/{id}', [\App\Http\Controllers\UrunController::class, 'destroy']);

    Route::get('/fiyat-teklifleri', [\App\Http\Controllers\FiyatTeklifController::class, 'index']);
    Route::get('/fiyat-teklifleri/yeni', [\App\Http\Controllers\FiyatTeklifController::class, 'create']);
    Route::post('/fiyat-teklifleri', [\App\Http\Controllers\FiyatTeklifController::class, 'store']);
    Route::get('/fiyat-teklifleri/{id}', [\App\Http\Controllers\FiyatTeklifController::class, 'show']);
    Route::delete('/fiyat-teklifleri/{id}', [\App\Http\Controllers\FiyatTeklifController::class, 'destroy']);
    Route::get('/api/musteriler/{id}/yetkililer', [\App\Http\Controllers\FiyatTeklifController::class, 'getYetkililer']);

    Route::get('/teklif-kosullari', [\App\Http\Controllers\TeklifKosuluController::class, 'index']);
    Route::post('/teklif-kosullari', [\App\Http\Controllers\TeklifKosuluController::class, 'store']);
    Route::get('/teklif-kosullari/{id}/edit', [\App\Http\Controllers\TeklifKosuluController::class, 'edit']);
    Route::put('/teklif-kosullari/{id}', [\App\Http\Controllers\TeklifKosuluController::class, 'update']);
    Route::delete('/teklif-kosullari/{id}', [\App\Http\Controllers\TeklifKosuluController::class, 'destroy']);
    Route::post('/teklif-kosullari/{id}/varsayilan', [\App\Http\Controllers\TeklifKosuluController::class, 'varsayilanYap']);
    Route::get('/api/teklif-kosullari', [\App\Http\Controllers\TeklifKosuluController::class, 'apiList']);

    Route::get('/ziyaretler', [\App\Http\Controllers\ZiyaretController::class, 'index']);
    Route::get('/ziyaretler/{id}/edit', [\App\Http\Controllers\ZiyaretController::class, 'edit']);
    Route::put('/ziyaretler/{id}', [\App\Http\Controllers\ZiyaretController::class, 'update']);
    Route::delete('/ziyaretler/{id}', [\App\Http\Controllers\ZiyaretController::class, 'destroy']);
    Route::post('/ziyaretler', [\App\Http\Controllers\ZiyaretController::class, 'store']);

    Route::get('/tum-isler', [\App\Http\Controllers\TumIslerController::class, 'index']);
    Route::post('/tum-isler', [\App\Http\Controllers\TumIslerController::class, 'store']);
    Route::get('/tum-isler/{id}/edit', [\App\Http\Controllers\TumIslerController::class, 'edit']);
    Route::get('/tum-isler/{id}/duplicate', [\App\Http\Controllers\TumIslerController::class, 'duplicate']);
    Route::put('/tum-isler/{id}', [\App\Http\Controllers\TumIslerController::class, 'update']);
    Route::delete('/tum-isler/{id}', [\App\Http\Controllers\TumIslerController::class, 'destroy']);
});
