<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Kisi;
use App\Models\Ziyaret;
use App\Models\TumIsler;

// Login/Logout Routes (no auth middleware)
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

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    
    // Ana sayfa - Yeni özelleştirilebilir dashboard
    Route::get('/', fn () => view('pages.dashboard'))->name('home');
    
    // Dashboard - Özelleştirilebilir widget sistemi (alias)
    Route::get('/dashboard', fn () => view('pages.dashboard'))->name('dashboard.index');
    
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
    
    // Yeni iş oluştur
    $yeniIs = new TumIsler();
    $yeniIs->name = $eskiIs->name;
    $yeniIs->musteri_id = $eskiIs->musteri_id;
    $yeniIs->marka_id = $eskiIs->marka_id;
    $yeniIs->tipi = 'Verilecek';
    $yeniIs->turu = $eskiIs->turu;
    $yeniIs->oncelik = $eskiIs->oncelik ?? 3;
    $yeniIs->teklif_tutari = $eskiIs->teklif_tutari;
    $yeniIs->teklif_doviz = $eskiIs->teklif_doviz;
    $yeniIs->lisans_bitis = $eskiIs->lisans_bitis; // Lisans bitiş tarihini kopyala
    $yeniIs->is_guncellenme_tarihi = now();
    $yeniIs->aciklama = 'Lisans yenileme - Önceki iş ID: ' . $eskiIs->id;
    $yeniIs->save();
    
    return response()->json([
        'success' => true,
        'message' => 'Yenileme kaydı oluşturuldu',
        'yeni_is' => $yeniIs
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
    
    \App\Models\Marka::create($validated);
    
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
    
    return redirect('/markalar')->with('message', 'Marka güncellendi.');
});
Route::delete('/markalar/{id}', function ($id) {
    $marka = \App\Models\Marka::findOrFail($id);
    $marka->delete();
    
    return redirect('/markalar')->with('message', 'Marka silindi.');
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
    ]);
    
    \App\Models\Musteri::create($validated);
    
    return redirect('/musteriler')->with('message', 'Müşteri başarıyla eklendi.');
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
    ]);
    
    $musteri->update($validated);
    
    return redirect('/musteriler')->with('message', 'Müşteri güncellendi.');
});
Route::delete('/musteriler/{id}', function ($id) {
    $musteri = \App\Models\Musteri::findOrFail($id);
    $musteri->delete();
    
    return redirect('/musteriler')->with('message', 'Müşteri silindi.');
});

// Kişiler routes
Route::get('/kisiler', fn () => view('kisiler.index'));
Route::get('/kisiler/{id}/edit', function ($id) {
    $kisi = \App\Models\Kisi::findOrFail($id);
    return view('kisiler.edit', compact('kisi'));
});
Route::put('/kisiler/{id}', function ($id) {
    $kisi = \App\Models\Kisi::findOrFail($id);
    
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
    
    return redirect('/kisiler')->with('message', 'Kişi silindi.');
});
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
        'ziyaret_ismi' => 'required|max:255',
        'musteri_id' => 'nullable|exists:musteriler,id',
        'ziyaret_tarihi' => 'nullable|date',
        'arama_tarihi' => 'nullable|date',
        'tur' => 'nullable|string',
        'durumu' => 'nullable|string',
        'ziyaret_notlari' => 'nullable|string',
    ]);
    
    $ziyaret->update($validated);
    
    return redirect('/ziyaretler')->with('message', 'Ziyaret güncellendi.');
});
Route::delete('/ziyaretler/{id}', function ($id) {
    $ziyaret = \App\Models\Ziyaret::findOrFail($id);
    $ziyaret->delete();
    
    return redirect('/ziyaretler')->with('message', 'Ziyaret silindi.');
});
Route::post('/ziyaretler', function () {
    $validated = request()->validate([
        'ziyaret_ismi' => 'required|max:255',
        'musteri_id' => 'nullable|exists:musteriler,id',
        'ziyaret_tarihi' => 'nullable|date',
        'arama_tarihi' => 'nullable|date',
        'tur' => 'nullable|string',
        'durumu' => 'nullable|string',
        'ziyaret_notlari' => 'nullable|string',
    ]);
    
    \App\Models\Ziyaret::create($validated);
    
    return redirect('/ziyaretler')->with('message', 'Ziyaret başarıyla eklendi.');
});


    // Tüm İşler routes
Route::get('/tum-isler', fn () => view('tum-isler.index'));
Route::post('/tum-isler', function () {
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
        'alis_tutari' => 'nullable|numeric',
        'kur' => 'nullable|numeric',
        'kapanis_tarihi' => 'nullable|date',
        'lisans_bitis' => 'nullable|date',
        'notlar' => 'nullable|string',
        'gecmis_notlar' => 'nullable|string',
        'aciklama' => 'nullable|string',
    ]);
    
    \App\Models\TumIsler::create($validated);
    
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
        'alis_tutari' => 'nullable|numeric',
        'kur' => 'nullable|numeric',
        'kapanis_tarihi' => 'nullable|date',
        'lisans_bitis' => 'nullable|date',
        'notlar' => 'nullable|string',
        'gecmis_notlar' => 'nullable|string',
        'aciklama' => 'nullable|string',
    ]);
    
    $is->update($validated);
    
    return redirect('/tum-isler')->with('message', 'İş güncellendi.');
});
Route::delete('/tum-isler/{id}', function ($id) {
    $is = \App\Models\TumIsler::findOrFail($id);
    $is->delete();
    
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
});
