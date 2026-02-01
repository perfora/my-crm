<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        $toplamZiyaretler = \App\Models\Ziyaret::count();
        $toplamIsler = \App\Models\TumIsler::count();
        
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
        $showBekleyenIsler = $widgetSettings['bekleyen_isler'] ?? true;
        $showBuAyKazanilan = $widgetSettings['bu_ay_kazanilan'] ?? true;
        $showYuksekOncelik = $widgetSettings['yuksek_oncelik'] ?? true;
        $showYaklasanZiyaretler = $widgetSettings['yaklasan_ziyaretler'] ?? true;
        
        // Widget Verileri
        $bekleyenIsler = \App\Models\TumIsler::whereIn('tipi', ['Teklif Aşamasında', 'Devam Edecek'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
            
        $buAyKazanilan = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
            ->whereMonth('kapanis_tarihi', date('m'))
            ->whereYear('kapanis_tarihi', date('Y'))
            ->orderBy('kapanis_tarihi', 'desc')
            ->limit(10)
            ->get();
            
        $yuksekOncelikIsler = \App\Models\TumIsler::where('oncelik', 'Yüksek')
            ->whereNotIn('tipi', ['Kazanıldı', 'Kaybedildi'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
            
        $yaklasanZiyaretler = \App\Models\Ziyaret::where('tarih', '>=', now())
            ->orderBy('tarih', 'asc')
            ->limit(10)
            ->get();
    @endphp

    <div class="container mx-auto px-4 py-8">
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
                        <p class="text-sm font-medium opacity-90">Toplam Ziyaret</p>
                        <p class="text-4xl font-bold mt-2">{{ $toplamZiyaretler }}</p>
                    </div>
                    <div class="text-5xl opacity-20">🚗</div>
                </div>
            </div>

            <!-- İşler -->
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium opacity-90">Toplam İş</p>
                        <p class="text-4xl font-bold mt-2">{{ $toplamIsler }}</p>
                    </div>
                    <div class="text-5xl opacity-20">📊</div>
                </div>
            </div>
        </div>

        <!-- Yıllık Karşılaştırma -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
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

        <!-- Widget'lar -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Bekleyen İşler -->
            @if($showBekleyenIsler)
            <div class="bg-white rounded-lg shadow-lg border-t-4 border-yellow-500">
                <div class="p-4 border-b bg-yellow-50">
                    <h3 class="text-xl font-bold text-yellow-800">⏳ Bekleyen İşler</h3>
                    <p class="text-sm text-gray-600">Teklif Aşamasında ve Devam Edecek</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">İş Adı</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Müşteri</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Durum</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Teklif</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bekleyenIsler as $is)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $is->name }}</td>
                                <td class="px-4 py-3">{{ $is->musteri->sirket ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs font-semibold {{ $is->tipi == 'Teklif Aşamasında' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $is->tipi }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-mono">${{ number_format($is->teklif_tutari, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">Bekleyen iş yok</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Bu Ay Kazanılan İşler -->
            @if($showBuAyKazanilan)
            <div class="bg-white rounded-lg shadow-lg border-t-4 border-green-500">
                <div class="p-4 border-b bg-green-50">
                    <h3 class="text-xl font-bold text-green-800">✅ Bu Ay Kazanılan İşler</h3>
                    <p class="text-sm text-gray-600">{{ date('F Y') }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">İş Adı</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Müşteri</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tarih</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Teklif</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($buAyKazanilan as $is)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $is->name }}</td>
                                <td class="px-4 py-3">{{ $is->musteri->sirket ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $is->kapanis_tarihi ? date('d.m.Y', strtotime($is->kapanis_tarihi)) : '-' }}</td>
                                <td class="px-4 py-3 text-right font-mono">${{ number_format($is->teklif_tutari, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">Bu ay kazanılan iş yok</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Yüksek Öncelikli İşler -->
            @if($showYuksekOncelik)
            <div class="bg-white rounded-lg shadow-lg border-t-4 border-red-500">
                <div class="p-4 border-b bg-red-50">
                    <h3 class="text-xl font-bold text-red-800">🔥 Yüksek Öncelikli İşler</h3>
                    <p class="text-sm text-gray-600">Acil takip gerektiren</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">İş Adı</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Müşteri</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Durum</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Teklif</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($yuksekOncelikIsler as $is)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $is->name }}</td>
                                <td class="px-4 py-3">{{ $is->musteri->sirket ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $is->tipi }}</td>
                                <td class="px-4 py-3 text-right font-mono">${{ number_format($is->teklif_tutari, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">Yüksek öncelikli iş yok</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Yaklaşan Ziyaretler -->
            @if($showYaklasanZiyaretler)
            <div class="bg-white rounded-lg shadow-lg border-t-4 border-purple-500">
                <div class="p-4 border-b bg-purple-50">
                    <h3 class="text-xl font-bold text-purple-800">📅 Yaklaşan Ziyaretler</h3>
                    <p class="text-sm text-gray-600">Gelecek ziyaret planları</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Müşteri</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tarih</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Notlar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($yaklasanZiyaretler as $ziyaret)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $ziyaret->musteri->sirket ?? '-' }}</td>
                                <td class="px-4 py-3">{{ date('d.m.Y', strtotime($ziyaret->tarih)) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ Str::limit($ziyaret->notlar ?? '-', 50) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500">Yaklaşan ziyaret yok</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
