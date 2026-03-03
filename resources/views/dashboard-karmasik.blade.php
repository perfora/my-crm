<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ana Sayfa - CRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    @include('layouts.nav')
    
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">CRM Dashboard</h1>
        
        @php
            $musteriler = \App\Models\Musteri::all();
            $kisiler = \App\Models\Kisi::all();
            $ziyaretler = \App\Models\Ziyaret::all();
            $isler = \App\Models\TumIsler::all();
            
            // 2025 ve 2026 kazanılan işler
            $kazanilan2025 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
                ->whereYear('kapanis_tarihi', 2025)
                ->get();
            
            $kazanilan2026 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
                ->whereYear('kapanis_tarihi', 2026)
                ->get();
            
            $toplamTeklif2025 = 0;
            $toplamTeklif2026 = 0;
            
            foreach($kazanilan2025 as $is) {
                if($is->teklif_doviz === 'USD' && $is->teklif_tutari) {
                    $toplamTeklif2025 += $is->teklif_tutari;
                }
            }
            
            foreach($kazanilan2026 as $is) {
                if($is->teklif_doviz === 'USD' && $is->teklif_tutari) {
                    $toplamTeklif2026 += $is->teklif_tutari;
                }
            }
        @endphp
        
        <!-- Özet Kartlar -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Toplam Müşteri</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $musteriler->count() }}</p>
                    </div>
                    <div class="text-4xl">🏢</div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Toplam Kişi</p>
                        <p class="text-3xl font-bold text-green-600">{{ $kisiler->count() }}</p>
                    </div>
                    <div class="text-4xl">👥</div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Toplam Ziyaret</p>
                        <p class="text-3xl font-bold text-purple-600">{{ $ziyaretler->count() }}</p>
                    </div>
                    <div class="text-4xl">📅</div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Toplam İş</p>
                        <p class="text-3xl font-bold text-orange-600">{{ $isler->count() }}</p>
                    </div>
                    <div class="text-4xl">💼</div>
                </div>
            </div>
        </div>
        
        <!-- Yıllık Kazanç Karşılaştırma -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">2025 Kazanılan İşler</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">İş Sayısı:</span>
                        <span class="font-bold text-lg">{{ $kazanilan2025->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Toplam Teklif:</span>
                        <span class="font-bold text-lg text-green-600">${{ number_format($toplamTeklif2025, 2) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">2026 Kazanılan İşler</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">İş Sayısı:</span>
                        <span class="font-bold text-lg">{{ $kazanilan2026->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Toplam Teklif:</span>
                        <span class="font-bold text-lg text-green-600">${{ number_format($toplamTeklif2026, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Widget Alanı - İstediğin widget'ları buraya ekle/çıkar -->
        <div class="space-y-6">
            
            <!-- Örnek 1: Bekleyen İşler -->
            @include('widgets.bekleyen-isler')
            
            <!-- Örnek 2: Bu Ay Kazanılan İşler -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>@include('widgets.bu-ay-kazanilan')</div>
                <div>@include('widgets.yuksek-oncelikli')</div>
            </div>
            
            <!-- Örnek 3: Yaklaşan Ziyaretler -->
            @include('widgets.yaklasan-ziyaretler')
            
            {{-- Kendi widget'ını buraya ekleyebilirsin --}}
            {{-- @include('widgets.kendi-widgetim') --}}
            
        </div>
    </div>
</body>
</html>
