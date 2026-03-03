<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    @include('layouts.nav')

    @php
        // Widget Ayarları
        $widgetSettings = [];
        $settingsPath = storage_path('app/widget-settings.json');
        if (file_exists($settingsPath)) {
            $widgetSettings = json_decode(file_get_contents($settingsPath), true) ?? [];
        }
        
        // Özet Veriler
        $toplamMusteri = \App\Models\Musteri::count();
        $toplamKisiler = \App\Models\Kisi::count();
        $toplamZiyaretler = \App\Models\Ziyaret::where('durumu', 'Tamamlandı')
            ->whereYear('ziyaret_tarihi', 2026)
            ->count();
        $toplamIsler = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
            ->whereYear('kapanis_tarihi', 2026)
            ->count();
        
        // 2024 Kazanılan İşler
        $isler2024 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
            ->whereYear('kapanis_tarihi', 2024)
            ->get();
        $adet2024 = $isler2024->count();
        $teklif2024 = $isler2024->sum('teklif_tutari');
        $alis2024 = $isler2024->sum('alis_tutari');
        $kar2024 = $teklif2024 - $alis2024;
        $karOran2024 = $teklif2024 > 0 ? ($kar2024 / $teklif2024) * 100 : 0;

        // 2025 Kazanılan İşler
        $isler2025 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
            ->whereYear('kapanis_tarihi', 2025)
            ->get();
        $adet2025 = $isler2025->count();
        $teklif2025 = $isler2025->sum('teklif_tutari');
        $alis2025 = $isler2025->sum('alis_tutari');
        $kar2025 = $teklif2025 - $alis2025;
        $karOran2025 = $teklif2025 > 0 ? ($kar2025 / $teklif2025) * 100 : 0;
        
        // 2026 Kazanılan İşler
        $isler2026 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
            ->whereYear('kapanis_tarihi', 2026)
            ->get();
        $adet2026 = $isler2026->count();
        $teklif2026 = $isler2026->sum('teklif_tutari');
        $alis2026 = $isler2026->sum('alis_tutari');
        $kar2026 = $teklif2026 - $alis2026;
        $karOran2026 = $teklif2026 > 0 ? ($kar2026 / $teklif2026) * 100 : 0;
        
        // Widget Görünürlüğü
        $showYaklasanZiyaretler = $widgetSettings['yaklasan_ziyaretler'] ?? true;
        
        // Widget Verileri
        $bekleyenIsler = \App\Models\TumIsler::where('oncelik', '1')
            ->where('tipi', 'Verilecek')
            ->whereYear('is_guncellenme_tarihi', 2026)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        $yaklasanZiyaretler = \App\Models\Ziyaret::whereIn('durumu', ['Beklemede', 'Planlandı'])
            ->orderBy('ziyaret_tarihi', 'asc')
            ->limit(8)
            ->get();

        // Register işleri: kapanış boş veya kapanış yılı 2026
        $registerIsleri = \App\Models\TumIsler::with('musteri')
            ->where('tipi', 'Register')
            ->where(function ($q) {
                $q->whereNull('kapanis_tarihi')
                    ->orWhereYear('kapanis_tarihi', 2026);
            })
            ->orderByRaw('kapanis_tarihi IS NULL DESC')
            ->orderBy('kapanis_tarihi', 'asc')
            ->limit(12)
            ->get();

        // Takip Edilecek işleri (2026 açılışlı)
        $takipEdilecekIsler = \App\Models\TumIsler::with('musteri')
            ->where('tipi', 'Takip Edilecek')
            ->whereYear('is_guncellenme_tarihi', 2026)
            ->orderByDesc('is_guncellenme_tarihi')
            ->limit(12)
            ->get();

        // Temas takip widget verileri (periyot farkı > 0 olanlar)
        $dereceSirasi = function ($derece) {
            if (preg_match('/^\s*([1-5])/', (string) $derece, $m)) {
                return (int) $m[1];
            }
            return 99;
        };

        $temasTakipRaw = \App\Models\Musteri::with(['ziyaretler'])
            ->where(function ($q) {
                $q->whereNotNull('temas_kurali')
                    ->orWhereNotNull('arama_periyodu_gun')
                    ->orWhereNotNull('ziyaret_periyodu_gun');
            })
            ->get()
            ->map(function ($musteri) use ($dereceSirasi) {
                $lastVisit = $musteri->ziyaretler
                    ->whereNotNull('ziyaret_tarihi')
                    ->max('ziyaret_tarihi');
                $lastCall = $musteri->ziyaretler
                    ->whereNotNull('arama_tarihi')
                    ->max('arama_tarihi');

                $lastVisit = $lastVisit ? \Carbon\Carbon::parse($lastVisit)->timezone(config('crm.timezone')) : null;
                $lastCall = $lastCall ? \Carbon\Carbon::parse($lastCall)->timezone(config('crm.timezone')) : null;

                $visitDays = $lastVisit ? (int) $lastVisit->diffInDays(now()) : null;
                $callDays = $lastCall ? (int) $lastCall->diffInDays(now()) : null;

                $visitPeriyot = $musteri->ziyaret_periyodu_gun ? (int) $musteri->ziyaret_periyodu_gun : null;
                $callPeriyot = $musteri->arama_periyodu_gun ? (int) $musteri->arama_periyodu_gun : null;

                // Hiç kayıt yoksa "bugün yapılmalı" kabul et ve +1 fark ile göster
                $visitOverdue = $visitPeriyot ? (($visitDays === null ? $visitPeriyot + 1 : $visitDays) - $visitPeriyot) : null;
                $callOverdue = $callPeriyot ? (($callDays === null ? $callPeriyot + 1 : $callDays) - $callPeriyot) : null;

                $musteri->tt_last_visit = $lastVisit;
                $musteri->tt_last_call = $lastCall;
                $musteri->tt_visit_overdue = $visitOverdue;
                $musteri->tt_call_overdue = $callOverdue;
                $musteri->tt_derece_rank = $dereceSirasi($musteri->derece ?? null);
                return $musteri;
            });

        $ziyaretGerekliList = $temasTakipRaw
            ->filter(function ($m) {
                return $m->temas_kurali === 'Ziyaret Öncelikli'
                    && (($m->tt_visit_overdue ?? 0) > 0)
                    && (($m->tt_derece_rank ?? 99) <= 3);
            })
            ->sort(function ($a, $b) {
                $aRank = (int) ($a->tt_derece_rank ?? 99);
                $bRank = (int) ($b->tt_derece_rank ?? 99);
                if ($aRank !== $bRank) {
                    return $aRank <=> $bRank; // 1,2,3
                }

                $aOverdue = (int) ($a->tt_visit_overdue ?? 0);
                $bOverdue = (int) ($b->tt_visit_overdue ?? 0);
                if ($aOverdue !== $bOverdue) {
                    return $bOverdue <=> $aOverdue; // aynı derecede çok geciken üstte
                }

                return strcasecmp((string) ($a->sirket ?? ''), (string) ($b->sirket ?? ''));
            })
            ->take(10);

        $ikisiGerekliList = $temasTakipRaw
            ->filter(function ($m) {
                if ($m->temas_kurali !== 'Her İkisi Zorunlu') {
                    return false;
                }
                return ((($m->tt_visit_overdue ?? 0) > 0) || (($m->tt_call_overdue ?? 0) > 0))
                    && (($m->tt_derece_rank ?? 99) <= 3);
            })
            ->sort(function ($a, $b) {
                $aRank = (int) ($a->tt_derece_rank ?? 99);
                $bRank = (int) ($b->tt_derece_rank ?? 99);
                if ($aRank !== $bRank) {
                    return $aRank <=> $bRank; // 1,2,3
                }

                $aScore = max((int) max(0, (int) ($a->tt_visit_overdue ?? 0)), (int) max(0, (int) ($a->tt_call_overdue ?? 0)));
                $bScore = max((int) max(0, (int) ($b->tt_visit_overdue ?? 0)), (int) max(0, (int) ($b->tt_call_overdue ?? 0)));
                if ($aScore !== $bScore) {
                    return $bScore <=> $aScore; // aynı derecede çok geciken üstte
                }

                return strcasecmp((string) ($a->sirket ?? ''), (string) ($b->sirket ?? ''));
            })
            ->take(10);

        $aramaGerekliList = $temasTakipRaw
            ->filter(function ($m) {
                return in_array($m->temas_kurali, ['Arama Yeterli', 'Şehir Dışı (Arama Öncelikli)'], true)
                    && (($m->tt_call_overdue ?? 0) > 0)
                    && (($m->tt_derece_rank ?? 99) <= 3);
            })
            ->sort(function ($a, $b) {
                $aRank = (int) ($a->tt_derece_rank ?? 99);
                $bRank = (int) ($b->tt_derece_rank ?? 99);
                if ($aRank !== $bRank) {
                    return $aRank <=> $bRank; // 1,2,3
                }

                $aOverdue = (int) ($a->tt_call_overdue ?? 0);
                $bOverdue = (int) ($b->tt_call_overdue ?? 0);
                if ($aOverdue !== $bOverdue) {
                    return $bOverdue <=> $aOverdue; // aynı derecede çok geciken üstte
                }

                return strcasecmp((string) ($a->sirket ?? ''), (string) ($b->sirket ?? ''));
            })
            ->take(10);

        $degreeDotClass = function (?string $derece): string {
            return match ($derece) {
                '1 -Sık' => 'bg-red-500',
                '2 - Orta' => 'bg-yellow-500',
                '3- Düşük' => 'bg-green-500',
                '4 - Potansiyel' => 'bg-blue-500',
                '5 - İş Ortağı' => 'bg-slate-500',
                default => 'bg-gray-400',
            };
        };
    @endphp

    <div class="container mx-auto px-6 py-8 max-w-screen-2xl">
        <!-- Başlık ve Butonlar -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800">🏠 CRM Dashboard</h1>
            <div class="flex gap-3">
                <a href="/dashboard-settings" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold transition">
                    ⚙️ Widget Ayarları
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold transition">
                        🚪 Çıkış
                    </button>
                </form>
            </div>
        </div>

        <!-- Özet Kartlar -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Müşteriler -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium opacity-90">Toplam Müşteri</p>
                        <p class="text-4xl font-bold mt-2">{{ $toplamMusteri }}</p>
                    </div>
                    <div class="text-5xl opacity-20">👥</div>
                </div>
            </div>

            <!-- Kişiler -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium opacity-90">Toplam Kişi</p>
                        <p class="text-4xl font-bold mt-2">{{ $toplamKisiler }}</p>
                    </div>
                    <div class="text-5xl opacity-20">👤</div>
                </div>
            </div>

            <!-- Ziyaretler -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium opacity-90">2026 Tamamlanan Ziyaret</p>
                        <p class="text-4xl font-bold mt-2">{{ $toplamZiyaretler }}</p>
                    </div>
                    <div class="text-5xl opacity-20">🚗</div>
                </div>
            </div>

            <!-- İşler -->
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium opacity-90">2026 Kazanılan İş</p>
                        <p class="text-4xl font-bold mt-2">{{ $toplamIsler }}</p>
                    </div>
                    <div class="text-5xl opacity-20">📊</div>
                </div>
            </div>
        </div>

        <!-- Yıllık Karşılaştırma -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- 2024 Kazanılan -->
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">📅 2024 Kazanılan İşler</h2>
                    <span class="text-3xl font-bold text-blue-600">{{ $adet2024 }}</span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-gray-700 font-semibold">Toplam Teklif:</span>
                        <span class="text-lg font-bold text-gray-900">${{ number_format($teklif2024, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-gray-700 font-semibold">Toplam Alış:</span>
                        <span class="text-lg font-bold text-gray-900">${{ number_format($alis2024, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded">
                        <span class="text-gray-700 font-semibold">Toplam Kar:</span>
                        <span class="text-lg font-bold text-green-600">${{ number_format($kar2024, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded">
                        <span class="text-gray-700 font-semibold">Kar Oranı:</span>
                        <span class="text-lg font-bold text-blue-600">%{{ number_format($karOran2024, 1) }}</span>
                    </div>
                </div>
            </div>

            <!-- 2025 Kazanılan -->
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">📅 2025 Kazanılan İşler</h2>
                    <span class="text-3xl font-bold text-indigo-600">{{ $adet2025 }}</span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-gray-700 font-semibold">Toplam Teklif:</span>
                        <span class="text-lg font-bold text-gray-900">${{ number_format($teklif2025, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-gray-700 font-semibold">Toplam Alış:</span>
                        <span class="text-lg font-bold text-gray-900">${{ number_format($alis2025, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded">
                        <span class="text-gray-700 font-semibold">Toplam Kar:</span>
                        <span class="text-lg font-bold text-green-600">${{ number_format($kar2025, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded">
                        <span class="text-gray-700 font-semibold">Kar Oranı:</span>
                        <span class="text-lg font-bold text-blue-600">%{{ number_format($karOran2025, 1) }}</span>
                    </div>
                </div>
            </div>

            <!-- 2026 Kazanılan -->
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-pink-500">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">📅 2026 Kazanılan İşler</h2>
                    <span class="text-3xl font-bold text-pink-600">{{ $adet2026 }}</span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-gray-700 font-semibold">Toplam Teklif:</span>
                        <span class="text-lg font-bold text-gray-900">${{ number_format($teklif2026, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-gray-700 font-semibold">Toplam Alış:</span>
                        <span class="text-lg font-bold text-gray-900">${{ number_format($alis2026, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded">
                        <span class="text-gray-700 font-semibold">Toplam Kar:</span>
                        <span class="text-lg font-bold text-green-600">${{ number_format($kar2026, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded">
                        <span class="text-gray-700 font-semibold">Kar Oranı:</span>
                        <span class="text-lg font-bold text-blue-600">%{{ number_format($karOran2026, 1) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-lg border-t-4 border-yellow-500">
                <div class="p-4 border-b bg-yellow-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold text-yellow-800">⏳ Bekleyen İşler</h3>
                            <p class="text-sm text-gray-600">Tipi Verilecek kayıtlar</p>
                        </div>
                        <span class="text-sm text-gray-600 font-semibold">{{ $bekleyenIsler->count() }} kayıt</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">İş Adı</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Müşteri</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bekleyenIsler as $is)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $is->name }}</td>
                                <td class="px-4 py-3">
                                    @if($is->musteri)
                                        <span class="inline-block w-2 h-2 rounded-full align-middle mr-2 {{ $degreeDotClass($is->musteri->derece) }}"></span>{{ $is->musteri->sirket }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-gray-500">Kayıt yok</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg border-t-4 border-amber-500">
                <div class="p-4 border-b bg-amber-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold text-amber-800">🧩 Register İşleri</h3>
                            <p class="text-sm text-gray-600">Tipi Register + kapanış boş veya 2026</p>
                        </div>
                        <span class="text-sm text-gray-600 font-semibold">{{ $registerIsleri->count() }} kayıt</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">İş</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kapanış</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registerIsleri as $is)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $is->name }}</td>
                                <td class="px-4 py-3">
                                    {{ $is->kapanis_tarihi ? \Carbon\Carbon::parse($is->kapanis_tarihi)->format('d.m.Y') : 'Kapanış Yok' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-gray-500">Kayıt yok</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg border-t-4 border-indigo-500">
                <div class="p-4 border-b bg-indigo-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold text-indigo-800">📌 Takip Edilecek İşler</h3>
                            <p class="text-sm text-gray-600">Tipi Takip Edilecek kayıtlar</p>
                        </div>
                        <span class="text-sm text-gray-600 font-semibold">{{ $takipEdilecekIsler->count() }} kayıt</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">İş</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Açılış</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($takipEdilecekIsler as $is)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $is->name }}</td>
                                <td class="px-4 py-3">{{ $is->is_guncellenme_tarihi ? \Carbon\Carbon::parse($is->is_guncellenme_tarihi)->format('d.m.Y') : '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-gray-500">Kayıt yok</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Yan Yana Widget'lar: Ziyaretler & Lisans -->
    <div class="container mx-auto px-6 py-8 max-w-screen-2xl">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-lg border-t-4 border-purple-500">
                <div class="p-4 border-b bg-purple-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-purple-800">👥 Ziyaret Gerekli</h3>
                    <span class="text-sm text-gray-600 font-semibold">{{ $ziyaretGerekliList->count() }} kayıt</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Müşteri</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Ziyaret</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Arama</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ziyaretGerekliList as $m)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="/musteriler/{{ $m->id }}" class="text-blue-600 hover:underline font-semibold">
                                        <span class="inline-block w-2 h-2 rounded-full align-middle mr-2 {{ $degreeDotClass($m->derece) }}"></span>{{ $m->sirket }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    @if($m->tt_last_visit)
                                        {{ $m->tt_last_visit->format(config('crm.date_format')) }} <span class="text-red-600 font-semibold">(+{{ $m->tt_visit_overdue }}g)</span>
                                    @else
                                        <span class="text-gray-500">Yok</span> <span class="text-red-600 font-semibold">(+{{ (int)($m->tt_visit_overdue ?? 0) }}g)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($m->tt_last_call)
                                        {{ $m->tt_last_call->format(config('crm.date_format')) }}
                                    @else
                                        <span class="text-gray-500">Yok</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">Kayıt yok</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg border-t-4 border-amber-500">
                <div class="p-4 border-b bg-amber-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-amber-800">🔁 İkisi Gerekli</h3>
                    <span class="text-sm text-gray-600 font-semibold">{{ $ikisiGerekliList->count() }} kayıt</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Müşteri</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Ziyaret</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Arama</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ikisiGerekliList as $m)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="/musteriler/{{ $m->id }}" class="text-blue-600 hover:underline font-semibold">
                                        <span class="inline-block w-2 h-2 rounded-full align-middle mr-2 {{ $degreeDotClass($m->derece) }}"></span>{{ $m->sirket }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    @if($m->tt_last_visit)
                                        {{ $m->tt_last_visit->format(config('crm.date_format')) }}
                                        @if(($m->tt_visit_overdue ?? 0) > 0)<span class="text-red-600 font-semibold">(+{{ $m->tt_visit_overdue }}g)</span>@endif
                                    @else
                                        <span class="text-gray-500">Yok</span>
                                        @if(($m->tt_visit_overdue ?? 0) > 0)<span class="text-red-600 font-semibold">(+{{ (int)$m->tt_visit_overdue }}g)</span>@endif
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($m->tt_last_call)
                                        {{ $m->tt_last_call->format(config('crm.date_format')) }}
                                        @if(($m->tt_call_overdue ?? 0) > 0)<span class="text-red-600 font-semibold">(+{{ $m->tt_call_overdue }}g)</span>@endif
                                    @else
                                        <span class="text-gray-500">Yok</span>
                                        @if(($m->tt_call_overdue ?? 0) > 0)<span class="text-red-600 font-semibold">(+{{ (int)$m->tt_call_overdue }}g)</span>@endif
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">Kayıt yok</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg border-t-4 border-green-500">
                <div class="p-4 border-b bg-green-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-green-800">📞 Arama Gerekli</h3>
                    <span class="text-sm text-gray-600 font-semibold">{{ $aramaGerekliList->count() }} kayıt</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Müşteri</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Ziyaret</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Arama</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($aramaGerekliList as $m)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="/musteriler/{{ $m->id }}" class="text-blue-600 hover:underline font-semibold">
                                        <span class="inline-block w-2 h-2 rounded-full align-middle mr-2 {{ $degreeDotClass($m->derece) }}"></span>{{ $m->sirket }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    @if($m->tt_last_visit)
                                        {{ $m->tt_last_visit->format(config('crm.date_format')) }}
                                    @else
                                        <span class="text-gray-500">Yok</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($m->tt_last_call)
                                        {{ $m->tt_last_call->format(config('crm.date_format')) }} <span class="text-red-600 font-semibold">(+{{ $m->tt_call_overdue }}g)</span>
                                    @else
                                        <span class="text-gray-500">Yok</span> <span class="text-red-600 font-semibold">(+{{ (int)($m->tt_call_overdue ?? 0) }}g)</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">Kayıt yok</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 py-8 max-w-screen-2xl">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Sol: Yaklaşan Ziyaretler -->
            @if($showYaklasanZiyaretler)
            <div class="bg-white rounded-lg shadow-lg border-t-4 border-purple-500 lg:col-span-1">
                <div class="p-4 border-b bg-purple-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold text-purple-800">📅 Bekleyen & Planlanan Ziyaretler</h3>
                            <p class="text-sm text-gray-600">Beklemede ve Planlandı durumunda</p>
                        </div>
                        <span class="text-sm text-gray-600 font-semibold">{{ $yaklasanZiyaretler->count() }} ziyaret</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Müşteri</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tarih</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($yaklasanZiyaretler as $ziyaret)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    @if($ziyaret->musteri)
                                        <span class="inline-block w-2 h-2 rounded-full align-middle mr-2 {{ $degreeDotClass($ziyaret->musteri->derece) }}"></span>{{ $ziyaret->musteri->sirket }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $ziyaret->ziyaret_tarihi ? \Carbon\Carbon::parse($ziyaret->ziyaret_tarihi)->format('d.m.Y') : '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs font-semibold {{ $ziyaret->durumu == 'Beklemede' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $ziyaret->durumu }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500">Bekleyen veya planlanan ziyaret yok</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Sağ: Lisans Yenilenecek İşler -->
            <div class="{{ $showYaklasanZiyaretler ? 'lg:col-span-3' : 'lg:col-span-4' }}">
                @include('widgets.lisans-yenilenecek')
            </div>
        </div>
    </div>
</body>
</html>
