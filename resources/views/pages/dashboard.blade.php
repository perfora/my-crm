<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100">
    @include('layouts.nav')

@php
    // Üst özetler
    $toplamMusteri = \App\Models\Musteri::count();
    $toplamIsler = \App\Models\TumIsler::count();
    
    // 2025 Kazanılan İşler
    $isler2025 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
        ->whereYear('kapanis_tarihi', 2025)
        ->get();
    $toplamKazanilanIsler2025 = $isler2025->count();
    $toplamTeklif2025 = $isler2025->sum('teklif_tutari');
    $toplamAlis2025 = $isler2025->sum('alis_tutari');
    $kar2025 = $toplamTeklif2025 - $toplamAlis2025;
    $karOran2025 = $toplamTeklif2025 > 0 ? ($kar2025 / $toplamTeklif2025) * 100 : 0;
    
    // 2026 Kazanılan İşler
    $isler2026 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
        ->whereYear('kapanis_tarihi', 2026)
        ->get();
    $toplamKazanilanIsler2026 = $isler2026->count();
    $toplamTeklif2026 = $isler2026->sum('teklif_tutari');
    $toplamAlis2026 = $isler2026->sum('alis_tutari');
    $kar2026 = $toplamTeklif2026 - $toplamAlis2026;
    $karOran2026 = $toplamTeklif2026 > 0 ? ($kar2026 / $toplamTeklif2026) * 100 : 0;
    
    $toplamTeklifAsamasinda = \App\Models\TumIsler::where('tipi', 'Teklif Aşamasında')->count();
@endphp

<div class="container mx-auto px-4 py-8">
    <!-- Üst Özet Kartları -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Toplam Müşteri -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <2025 Kazanılan İşler -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="text-sm font-medium opacity-90">2025 Kazanılan</p>
                    <p class="text-4xl font-bold mt-2">{{ $toplamKazanilanIsler2025 }}</p>
                </div>
                <div class="text-5xl opacity-20">✅</div>
            </div>
            <div class="border-t border-white/20 pt-3 mt-2 text-sm space-y-1">
                <div class="flex justify-between">
                    <span class="opacity-90">Teklif:</span>
                    <span class="font-semibold">${{ number_format($toplamTeklif2025, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="opacity-90">Kar:</span>
                    <span class="font-semibold">${{ number_format($kar2025, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="opacity-90">Oran:</span>
                    <span class="font-semibold">%{{ number_format($karOran2025, 1) }}</span>
                </div>
            </div>
        </div>

        <!-- 2026 Kazanılan İşler -->
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="text-sm font-medium opacity-90">2026 Kazanılan</p>
                    <p class="text-4xl font-bold mt-2">{{ $toplamKazanilanIsler2026 }}</p>
                </div>
                <div class="text-5xl opacity-20">✅</div>
            </div>
            <div class="border-t border-white/20 pt-3 mt-2 text-sm space-y-1">
                <div class="flex justify-between">
                    <span class="opacity-90">Teklif:</span>
                    <span class="font-semibold">${{ number_format($toplamTeklif2026, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="opacity-90">Kar:</span>
                    <span class="font-semibold">${{ number_format($kar2026, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="opacity-90">Oran:</span>
                    <span class="font-semibold">%{{ number_format($karOran2026, 1) }}</span>
                

        <!-- Kazanılan İşler -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium opacity-90">Kazanılan İş</p>
                    <p class="text-4xl font-bold mt-2">{{ $toplamKazanilanIsler }}</p>
                </div>
                <div class="text-5xl opacity-20">✅</div>
            </div>
        </div>

        <!-- Teklif Aşamasında -->
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium opacity-90">Teklif Aşamasında</p>
                    <p class="text-4xl font-bold mt-2">{{ $toplamTeklifAsamasinda }}</p>
                </div>
                <div class="text-5xl opacity-20">📋</div>
            </div>
        </div>
    </div>

    <!-- Widget Sistemi (Notion-like) -->
    <div class="mt-12">
        <livewire:dashboard-manager />
    </div>
</div>

@livewireScripts
</body>
</html>