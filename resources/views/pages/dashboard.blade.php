<x-layouts::app.sidebar>
@php
    // Üst özetler - 2025 ve 2026 verileri
    $isler2025 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
        ->whereYear('kapanis_tarihi', 2025)
        ->get();
    $adet2025 = $isler2025->count();
    $toplamTeklif2025 = $isler2025->sum('teklif_tutari');
    $toplamAlış2025 = $isler2025->sum('alis_tutari');
    $kar2025 = $toplamTeklif2025 - $toplamAlış2025;
    
    $isler2026 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
        ->whereYear('kapanis_tarihi', 2026)
        ->get();
    $adet2026 = $isler2026->count();
    $toplamTeklif2026 = $isler2026->sum('teklif_tutari');
    $toplamAlış2026 = $isler2026->sum('alis_tutari');
    $kar2026 = $toplamTeklif2026 - $toplamAlış2026;
    
    // Toplam müşteri ve işler
    $toplamMusteri = \App\Models\Musteri::count();
    $toplamIsler = \App\Models\TumIsler::count();
@endphp

<flux:main>
<div class="container mx-auto px-4 py-8">
    <!-- Üst Özet Kartları -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <!-- Toplam Müşteri -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-gray-600 text-sm font-semibold uppercase">Toplam Müşteri</p>
            <p class="text-3xl font-bold text-blue-600">{{ $toplamMusteri }}</p>
        </div>

        <!-- Toplam İş -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <p class="text-gray-600 text-sm font-semibold uppercase">Toplam İş</p>
            <p class="text-3xl font-bold text-green-600">{{ $toplamIsler }}</p>
        </div>

        <!-- 2025 Karlılık -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <p class="text-gray-600 text-sm font-semibold uppercase">2025 Kazanılan</p>
            <p class="text-3xl font-bold text-purple-600">{{ $adet2025 }}</p>
            <p class="text-sm text-gray-700 mt-2">Teklif: ${{ number_format($toplamTeklif2025, 2, ',', '.') }}</p>
            <p class="text-sm text-gray-700">Kar: ${{ number_format($kar2025, 2, ',', '.') }}</p>
        </div>

        <!-- 2026 Karlılık -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
            <p class="text-gray-600 text-sm font-semibold uppercase">2026 Kazanılan</p>
            <p class="text-3xl font-bold text-orange-600">{{ $adet2026 }}</p>
            <p class="text-sm text-gray-700 mt-2">Teklif: ${{ number_format($toplamTeklif2026, 2, ',', '.') }}</p>
            <p class="text-sm text-gray-700">Kar: ${{ number_format($kar2026, 2, ',', '.') }}</p>
        </div>
    </div>

    <!-- Widget Sistemi (Notion-like) -->
    <div class="mt-12">
        <livewire:dashboard-manager />
    </div>
</flux:main>
</x-layouts::app.sidebar>
