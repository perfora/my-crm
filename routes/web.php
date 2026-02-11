<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Kisi;
use App\Models\Ziyaret;
use App\Models\TumIsler;
use App\Models\SystemLog;
use App\Models\ChangeJournal;
use App\Support\LogSanitizer;

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

// Login/Logout Routes (no auth middleware)
Route::get('/finans', function () {
    return view('finans');
})->name('finans');

Route::get('/login', function() {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/login', function(Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);
    
    // Rate limiting: 5 deneme / 1 dakika
    $key = 'login.' . $request->ip();
    $maxAttempts = 5;
    $decayMinutes = 1;
    
    if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
        $seconds = RateLimiter::availableIn($key);
        return back()->withErrors([
            'email' => "Çok fazla başarısız deneme! {$seconds} saniye sonra tekrar deneyin.",
        ])->onlyInput('email');
    }
    
    if (auth()->attempt($credentials, $request->filled('remember'))) {
        RateLimiter::clear($key); // Başarılı girişte sayacı sıfırla
        $request->session()->regenerate();
        return redirect()->intended('/');
    }
    
    RateLimiter::hit($key, $decayMinutes * 60); // Başarısız deneme kaydet
    
    return back()->withErrors([
        'email' => 'E-posta veya şifre hatalı.',
    ])->onlyInput('email');
});

Route::post('/logout', function() {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Static asset compatibility routes for environments serving files under /public
Route::get('/favicon.ico', fn () => redirect('/public/favicon.ico', 301));
Route::get('/favicon.svg', fn () => redirect('/public/favicon.svg', 301));
Route::get('/apple-touch-icon.png', fn () => redirect('/public/apple-touch-icon.png', 301));

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    
    // Ana sayfa - Telefon algılama ile yönlendirme
    Route::get('/', function () {
        $userAgent = request()->header('User-Agent');
        $isMobile = preg_match('/(android|iphone|ipad|mobile)/i', $userAgent);
        
        if ($isMobile) {
            return redirect('/mobile');
        }
        
        return view('pages.dashboard');
    })->name('home');
    
    // Mobil Routes
    Route::prefix('mobile')->group(function () {
        Route::get('/', fn () => view('mobile.index'))->name('mobile.index');
        Route::get('/yeni-is', fn () => view('mobile.yeni-is'))->name('mobile.yeni-is');
        Route::get('/yeni-ziyaret', fn () => view('mobile.yeni-ziyaret'))->name('mobile.yeni-ziyaret');
        Route::get('/planli-kayitlar', fn () => view('mobile.planli-kayitlar'))->name('mobile.planli-kayitlar');
        Route::get('/hizli-kayit', fn () => view('mobile.hizli-kayit'))->name('mobile.hizli-kayit');
        Route::post('/hizli-kayit', function () {
            $validated = request()->validate([
                'musteri_id' => 'required|exists:musteriler,id',
                'contact_type' => 'required|in:Telefon,Ziyaret',
                'ziyaret_notlari' => 'nullable|string',
            ]);

            $musteri = \App\Models\Musteri::findOrFail($validated['musteri_id']);
            $now = \Carbon\Carbon::now('Europe/Istanbul');
            $isTelefon = $validated['contact_type'] === 'Telefon';

            \App\Models\Ziyaret::create([
                'ziyaret_ismi' => $musteri->sirket . ' ' . ($isTelefon ? 'Arama' : 'Ziyaret'),
                'musteri_id' => $musteri->id,
                'ziyaret_tarihi' => $isTelefon ? null : $now,
                'arama_tarihi' => $isTelefon ? $now : null,
                'gerceklesen_tarih' => $now,
                'tur' => $validated['contact_type'],
                'durumu' => 'Tamamlandı',
                'ziyaret_notlari' => $validated['ziyaret_notlari'] ?? null,
            ]);

            return redirect('/mobile/hizli-kayit')->with('message', 'Hızlı kayıt oluşturuldu.');
        })->name('mobile.hizli-kayit.store');
        Route::post('/ziyaretler/{id}/tamamla', function ($id) {
            $ziyaret = \App\Models\Ziyaret::findOrFail($id);
            $validated = request()->validate([
                'ziyaret_notlari' => 'nullable|string',
            ]);

            $updateData = [
                'durumu' => 'Tamamlandı',
                'gerceklesen_tarih' => \Carbon\Carbon::now('Europe/Istanbul'),
            ];

            $newNote = trim((string) ($validated['ziyaret_notlari'] ?? ''));
            if ($newNote !== '') {
                $oldNote = trim((string) ($ziyaret->ziyaret_notlari ?? ''));
                $updateData['ziyaret_notlari'] = $oldNote === '' ? $newNote : $oldNote . "\n\n" . $newNote;
            }

            $ziyaret->update($updateData);

            return redirect('/mobile/planli-kayitlar')->with('message', 'Kayıt tamamlandı.');
        })->name('mobile.planli-kayitlar.tamamla');
        Route::get('/raporlar', fn () => view('mobile.raporlar'))->name('mobile.raporlar');
    });
    
    // Dashboard - Özelleştirilebilir widget sistemi (alias)
    Route::get('/dashboard', fn () => view('pages.dashboard'))->name('dashboard.index');

    // System logs screen
    Route::get('/sistem-loglari', function (Request $request) {
        $query = SystemLog::query()->latest('id');
        if ($request->filled('channel')) {
            $query->where('channel', $request->string('channel'));
        }
        if ($request->filled('level')) {
            $query->where('level', $request->string('level'));
        }
        if ($request->filled('q')) {
            $q = (string) $request->string('q');
            $query->where(function ($inner) use ($q) {
                $inner->where('message', 'like', '%'.$q.'%')
                    ->orWhere('url', 'like', '%'.$q.'%')
                    ->orWhere('source', 'like', '%'.$q.'%');
            });
        }

        return view('sistem-loglari.index', [
            'logs' => $query->limit(300)->get(),
        ]);
    })->name('system-logs.index');

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

// API: Yenileme Kaydı Aç
Route::post('/api/yenileme-ac', function(Request $request) {
    $eskiIsId = $request->input('is_id');
    $eskiIs = TumIsler::findOrFail($eskiIsId);

    if (DB::table('lisans_yenileme_kayitlari')->where('source_is_id', $eskiIs->id)->exists()) {
        return response()->json([
            'success' => true,
            'already_processed' => true,
            'message' => 'Bu kayıt zaten işlendi'
        ]);
    }
    
    // Yeni iş oluştur
    $yeniIs = new TumIsler();
    $yeniIs->name = $eskiIs->name;
    $yeniIs->musteri_id = $eskiIs->musteri_id;
    $yeniIs->marka_id = $eskiIs->marka_id;
    $yeniIs->tipi = 'Verilecek';
    $yeniIs->oncelik = 1;
    $yeniIs->teklif_tutari = $eskiIs->teklif_tutari;
    $yeniIs->teklif_doviz = $eskiIs->teklif_doviz;
    $yeniIs->lisans_bitis = null;
    $yeniIs->is_guncellenme_tarihi = now();
    $yeniIs->aciklama = 'Lisans yenileme - Önceki iş ID: ' . $eskiIs->id;
    $yeniIs->save();

    DB::table('lisans_yenileme_kayitlari')->insert([
        'source_is_id' => $eskiIs->id,
        'created_is_id' => $yeniIs->id,
        'durum' => 'created',
        'user_id' => auth()->id(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Yenileme kaydı oluşturuldu',
        'yeni_is' => $yeniIs
    ]);
});

// API: Elle Açıldı Olarak İşaretle
Route::post('/api/yenileme-isaretle', function(Request $request) {
    $eskiIsId = $request->input('is_id');
    TumIsler::findOrFail($eskiIsId);

    DB::table('lisans_yenileme_kayitlari')->updateOrInsert(
        ['source_is_id' => $eskiIsId],
        [
            'durum' => 'opened',
            'user_id' => auth()->id(),
            'updated_at' => now(),
            'created_at' => now(),
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Kayıt işlendi'
    ]);
});

// API: Marka Bazında Rapor
Route::post('/api/rapor-marka', function(Request $request) {
    $yil = $request->input('yil', date('Y'));
    
    $rapor = \DB::select("
        SELECT 
            m.name as marka,
            COUNT(t.id) as adet,
            SUM(CASE 
                WHEN t.teklif_doviz = 'TL' THEN t.teklif_tutari / 35
                ELSE t.teklif_tutari 
            END) as toplam_teklif,
            SUM(CASE 
                WHEN t.alis_doviz = 'TL' THEN t.alis_tutari / 35
                ELSE t.alis_tutari 
            END) as toplam_alis,
            SUM(CASE 
                WHEN t.teklif_doviz = 'TL' THEN t.teklif_tutari / 35
                ELSE t.teklif_tutari 
            END) - SUM(CASE 
                WHEN t.alis_doviz = 'TL' THEN t.alis_tutari / 35
                ELSE t.alis_tutari 
            END) as toplam_kar
        FROM tum_isler t
        LEFT JOIN markalar m ON t.marka_id = m.id
        WHERE t.tipi = 'Kazanıldı'
        AND strftime('%Y', t.kapanis_tarihi) = ?
        GROUP BY t.marka_id, m.name
        ORDER BY toplam_kar DESC
    ", [$yil]);
    
    return response()->json($rapor);
});

// API: Müşteri Bazında Rapor
Route::post('/api/rapor-musteri', function(Request $request) {
    $yil = $request->input('yil', date('Y'));
    
    $rapor = \DB::select("
        SELECT 
            m.id as musteri_id,
            m.sirket as musteri,
            COUNT(t.id) as adet,
            SUM(CASE 
                WHEN t.teklif_doviz = 'TL' THEN t.teklif_tutari / 35
                ELSE t.teklif_tutari 
            END) as toplam_teklif,
            SUM(CASE 
                WHEN t.teklif_doviz = 'TL' THEN t.teklif_tutari / 35
                ELSE t.teklif_tutari 
            END) - SUM(CASE 
                WHEN t.alis_doviz = 'TL' THEN t.alis_tutari / 35
                ELSE t.alis_tutari 
            END) as toplam_kar,
            (SELECT COUNT(*) FROM ziyaretler z 
             WHERE z.musteri_id = m.id 
             AND z.durumu = 'Tamamlandı'
             AND z.tur = 'Ziyaret'
             AND strftime('%Y', z.ziyaret_tarihi) = ?) as ziyaret_adedi,
            (SELECT COUNT(*) FROM ziyaretler z 
             WHERE z.musteri_id = m.id 
             AND z.durumu = 'Tamamlandı'
             AND z.tur = 'Telefon'
             AND strftime('%Y', z.arama_tarihi) = ?) as arama_adedi
        FROM tum_isler t
        LEFT JOIN musteriler m ON t.musteri_id = m.id
        WHERE t.tipi = 'Kazanıldı'
        AND strftime('%Y', t.kapanis_tarihi) = ?
        GROUP BY t.musteri_id, m.sirket, m.id
        ORDER BY toplam_kar DESC
    ", [$yil, $yil, $yil]);
    
    return response()->json($rapor);
});

// Notion Ayarları
Route::get('/notion-settings', function() {
    $settings = DB::table('notion_settings')->pluck('value', 'key')->toArray();
    return view('notion-settings', compact('settings'));
});

Route::post('/notion-settings/update', function() {
    $key = request('key');
    $value = request('value');
    
    DB::table('notion_settings')
        ->where('key', $key)
        ->update(['value' => $value, 'updated_at' => now()]);
    
    // .env'i de güncelle (api_token için)
    if($key === 'api_token') {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        
        if(str_contains($envContent, 'NOTION_API_TOKEN=')) {
            $envContent = preg_replace('/NOTION_API_TOKEN=.*/', "NOTION_API_TOKEN={$value}", $envContent);
        } else {
            $envContent .= "\nNOTION_API_TOKEN={$value}\n";
        }
        
        file_put_contents($envFile, $envContent);
    }
    
    return redirect('/notion-settings')->with('success', 'Ayar kaydedildi!');
});

Route::post('/notion-settings/sync', function() {
    $type = request('type');
    $settings = DB::table('notion_settings')->pluck('value', 'key')->toArray();
    
    $databaseId = $settings["{$type}_db_id"] ?? null;
    
    if(!$databaseId) {
        return redirect('/notion-settings')->with('error', 'Database ID bulunamadı!');
    }
    
    try {
        Artisan::call('notion:sync', [
            'database_id' => $databaseId,
            '--type' => $type
        ]);
        
        $output = Artisan::output();
        return redirect('/notion-settings')->with('success', "✅ Sync tamamlandı!\n\n{$output}");
    } catch(\Exception $e) {
        return redirect('/notion-settings')->with('error', "❌ Hata: " . $e->getMessage());
    }
});

Route::post('/notion-settings/push', function() {
    $type = request('type');
    $settings = DB::table('notion_settings')->pluck('value', 'key')->toArray();
    
    $databaseId = $settings["{$type}_db_id"] ?? null;
    
    if(!$databaseId) {
        return redirect('/notion-settings')->with('error', 'Database ID bulunamadı!');
    }
    
    try {
        Artisan::call('notion:push', [
            'database_id' => $databaseId,
            '--type' => $type
        ]);
        
        $output = Artisan::output();
        return redirect('/notion-settings')->with('success', "✅ Push tamamlandı!\n\n{$output}");
    } catch(\Exception $e) {
        return redirect('/notion-settings')->with('error', "❌ Hata: " . $e->getMessage());
    }
});

// Widget Ayarları
Route::get('/dashboard/widget-settings', fn () => view('dashboard-settings'));
Route::post('/dashboard/widget-settings', function() {
    $widgets = request('widgets', []);
    $order = json_decode(request('order', '[]'), true);
    
    // Her widget için true/false ayarla
    $settings = [];
    foreach(['ozet_kartlar', 'yillik_karsilastirma', 'bekleyen_isler', 'bu_ay_kazanilan', 'yuksek_oncelikli', 'yaklasan_ziyaretler'] as $key) {
        $settings[$key] = isset($widgets[$key]);
    }
    
    // Sıralamayı kaydet
    if(!empty($order)) {
        $settings['order'] = $order;
    }
    
    // Dosyaya kaydet
    file_put_contents(storage_path('app/widget-settings.json'), json_encode($settings, JSON_PRETTY_PRINT));
    
    return redirect('/dashboard/widget-settings')->with('success', 'Widget ayarları kaydedildi!');
});

// Markalar routes
Route::get('/markalar', fn () => view('markalar.index'));
Route::get('/markalar/{id}', function ($id) {
    $marka = \App\Models\Marka::findOrFail($id);
    return view('markalar.show', compact('marka'));
});
Route::post('/markalar', function () {
    $validated = request()->validate([
        'name' => 'required|max:255',
    ]);
    
    $marka = \App\Models\Marka::create($validated);
    
    // AJAX inline creation için
    if (request()->ajax() || request()->wantsJson()) {
        return response()->json(['success' => true, 'data' => $marka]);
    }
    
    return redirect('/markalar')->with('message', 'Marka başarıyla eklendi.');
});
Route::get('/markalar/{id}/edit', function ($id) {
    $marka = \App\Models\Marka::findOrFail($id);
    return view('markalar.edit', compact('marka'));
});
Route::put('/markalar/{id}', function ($id) {
    $marka = \App\Models\Marka::findOrFail($id);
    
    $validated = request()->validate([
        'name' => 'required|max:255',
    ]);
    
    $marka->update($validated);

    if (request()->ajax() || request()->wantsJson()) {
        return response()->json(['success' => true, 'data' => $marka]);
    }
    
    return redirect('/markalar')->with('message', 'Marka güncellendi.');
});
Route::delete('/markalar/{id}', function ($id) {
    $marka = \App\Models\Marka::findOrFail($id);
    $marka->delete();

    if (request()->ajax() || request()->wantsJson()) {
        return response()->json(['success' => true]);
    }
    
    return redirect('/markalar')->with('message', 'Marka silindi.');
});

// İş Tipleri, Türleri ve Öncelik Routes (AJAX inline creation için)
Route::post('/is-tipleri', function () {
    $validated = request()->validate(['name' => 'required|max:255|unique:is_tipleri']);
    $tip = \App\Models\IsTipi::create($validated);
    
    if (request()->ajax() || request()->wantsJson()) {
        return response()->json(['success' => true, 'data' => $tip]);
    }
    return back()->with('message', 'İş tipi eklendi.');
});

Route::post('/is-turleri', function () {
    $validated = request()->validate(['name' => 'required|max:255|unique:is_turleri']);
    $tur = \App\Models\IsTuru::create($validated);
    
    if (request()->ajax() || request()->wantsJson()) {
        return response()->json(['success' => true, 'data' => $tur]);
    }
    return back()->with('message', 'İş türü eklendi.');
});

Route::post('/oncelikler', function () {
    $validated = request()->validate(['name' => 'required|max:255|unique:oncelikler']);
    $oncelik = \App\Models\Oncelik::create($validated);
    
    if (request()->ajax() || request()->wantsJson()) {
        return response()->json(['success' => true, 'data' => $oncelik]);
    }
    return back()->with('message', 'Öncelik eklendi.');
});

// Müşteriler (Firmalar) routes
Route::get('/musteriler', fn () => view('musteriler.index'));
Route::get('/raporlar', fn () => view('raporlar.index'));
Route::get('/musteriler/import', function() {
    $csv = storage_path('app/firmalar.csv');
    $data = array_map('str_getcsv', file($csv));
    $header = array_shift($data);
    $imported = 0;
    foreach ($data as $row) {
        $record = array_combine($header, $row);
        if (!empty($record['Şirket'])) {
            \App\Models\Musteri::firstOrCreate(
                ['sirket' => $record['Şirket']],
                [
                    'sehir' => $record['Şehir'] ?? null,
                    'adres' => $record['Adres'] ?? null,
                    'telefon' => $record['Telefon'] ?? null,
                    'notlar' => $record['Notlar'] ?? null,
                    'derece' => $record['Derece'] ?? null,
                    'turu' => $record['Türü'] ?? null,
                ]
            );
            $imported++;
        }
    }
    $total = \App\Models\Musteri::count();
    return redirect('/musteriler')->with('message', "✓ $imported firma kontrol edildi. Toplam: $total müşteri");
});
Route::post('/musteriler', function () {
    $validated = request()->validate([
        'sirket' => 'required|max:255',
        'sehir' => 'nullable|string',
        'adres' => 'nullable|string',
        'telefon' => 'nullable|string',
        'notlar' => 'nullable|string',
        'derece' => 'nullable|string',
        'turu' => 'nullable|string',
        'arama_periyodu_gun' => 'nullable|integer|min:1|max:3650',
        'ziyaret_periyodu_gun' => 'nullable|integer|min:1|max:3650',
        'temas_kurali' => 'nullable|string|max:50',
    ]);
    
    $musteri = \App\Models\Musteri::create($validated);
    
    // AJAX inline creation için
    if (request()->ajax() || request()->wantsJson()) {
        return response()->json(['success' => true, 'data' => $musteri]);
    }
    
    return redirect('/musteriler')->with('message', 'Müşteri başarıyla eklendi.');
});
Route::post('/musteriler/{id}/quick-contact', function ($id) {
    $musteri = \App\Models\Musteri::findOrFail($id);

    $validated = request()->validate([
        'contact_type' => 'required|in:Telefon,Ziyaret',
    ]);

    $now = \Carbon\Carbon::now('Europe/Istanbul');
    $isTelefon = $validated['contact_type'] === 'Telefon';

    $ziyaret = \App\Models\Ziyaret::create([
        'ziyaret_ismi' => $musteri->sirket . ' ' . ($isTelefon ? 'Arama' : 'Ziyaret'),
        'musteri_id' => $musteri->id,
        'ziyaret_tarihi' => $isTelefon ? null : $now,
        'arama_tarihi' => $isTelefon ? $now : null,
        'gerceklesen_tarih' => $now,
        'tur' => $validated['contact_type'],
        'durumu' => 'Tamamlandı',
        'ziyaret_notlari' => null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Hızlı kayıt oluşturuldu.',
        'data' => [
            'id' => $ziyaret->id,
            'musteri_id' => $musteri->id,
            'musteri' => $musteri->sirket,
            'contact_type' => $validated['contact_type'],
            'created_at' => $now->toDateTimeString(),
        ],
    ]);
});
Route::post('/ziyaretler/{id}/quick-note', function ($id) {
    $ziyaret = \App\Models\Ziyaret::findOrFail($id);
    $validated = request()->validate([
        'ziyaret_notlari' => 'required|string',
    ]);

    $ziyaret->update([
        'ziyaret_notlari' => $validated['ziyaret_notlari'],
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Not kaydedildi.',
        'data' => [
            'id' => $ziyaret->id,
            'ziyaret_notlari' => $ziyaret->ziyaret_notlari,
        ],
    ]);
});
Route::get('/musteriler/{id}', function ($id) {
    $musteri = \App\Models\Musteri::findOrFail($id);
    $kisiler = Kisi::where('musteri_id', $musteri->id)->get();
    $ziyaretler = Ziyaret::where('musteri_id', $musteri->id)->orderBy('ziyaret_tarihi', 'desc')->get();
    $isler = TumIsler::where('musteri_id', $musteri->id)->get();
    $kazanilanTotal = $isler->where('tipi', 'Kazanıldı')->sum(function($i) {
        return ($i->teklif_doviz === 'USD' || $i->alis_doviz === 'USD') ? $i->kar_tutari : 0;
    });
    return view('musteriler.show', compact('musteri', 'kisiler', 'ziyaretler', 'isler', 'kazanilanTotal'));
});
Route::get('/musteriler/{id}/edit', function ($id) {
    $musteri = \App\Models\Musteri::findOrFail($id);
    return view('musteriler.edit', compact('musteri'));
});
Route::put('/musteriler/{id}', function ($id) {
    $musteri = \App\Models\Musteri::findOrFail($id);
    
    // AJAX inline editing için
    if (request()->ajax() || request()->wantsJson()) {
        $validated = request()->validate([
            'sirket' => 'sometimes|required|max:255',
            'sehir' => 'nullable|string',
            'adres' => 'nullable|string',
            'telefon' => 'nullable|string',
            'notlar' => 'nullable|string',
            'derece' => 'nullable|string',
            'turu' => 'nullable|string',
            'arama_periyodu_gun' => 'nullable|integer|min:1|max:3650',
            'ziyaret_periyodu_gun' => 'nullable|integer|min:1|max:3650',
            'temas_kurali' => 'nullable|string|max:50',
        ]);
        
        $musteri->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Güncellendi',
            'data' => $musteri
        ]);
    }
    
    // Normal form submit için
    $validated = request()->validate([
        'sirket' => 'required|max:255',
        'sehir' => 'nullable|string',
        'adres' => 'nullable|string',
        'telefon' => 'nullable|string',
        'notlar' => 'nullable|string',
        'derece' => 'nullable|string',
        'turu' => 'nullable|string',
        'arama_periyodu_gun' => 'nullable|integer|min:1|max:3650',
        'ziyaret_periyodu_gun' => 'nullable|integer|min:1|max:3650',
        'temas_kurali' => 'nullable|string|max:50',
    ]);
    
    $musteri->update($validated);
    
    return redirect('/musteriler')->with('message', 'Müşteri güncellendi.');
});
Route::delete('/musteriler/{id}', function ($id) {
    $musteri = \App\Models\Musteri::findOrFail($id);
    $musteri->delete();
    
    if (request()->ajax()) {
        return response()->json(['success' => true, 'message' => 'Müşteri silindi.']);
    }
    
    return redirect('/musteriler')->with('message', 'Müşteri silindi.');
});

// Turu silme route'u
Route::post('/musteriler/delete-turu', function () {
    $turu = request('turu');
    
    // Bu türe sahip tüm müşterilerde turu null yap
    \App\Models\Musteri::where('turu', $turu)->update(['turu' => null]);
    
    return response()->json(['success' => true, 'message' => 'Tür silindi.']);
});

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

    // Varsayılan döviz: kullanıcı seçmediyse ve tutar girildiyse USD kabul et
    if (!empty($validated['teklif_tutari']) && empty($validated['teklif_doviz'])) {
        $validated['teklif_doviz'] = 'USD';
    }
    if (!empty($validated['alis_tutari']) && empty($validated['alis_doviz'])) {
        $validated['alis_doviz'] = 'USD';
    }
    
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
