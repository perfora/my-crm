<x-layouts::app.sidebar>
@php
    // Üst özetler - 2025 ve 2026 verileri
    $isler2025 = \App\Models\TumIsler::whereYear('is_guncellenme_tarihi', 2025)
        ->where('tipi', 'Kazanıldı')
        ->get();
    $toplamTeklif2025 = $isler2025->sum('teklif');
    $kar2025 = $isler2025->sum('kar');
    $karOrani2025 = $toplamTeklif2025 > 0 ? ($kar2025 / $toplamTeklif2025 * 100) : 0;
    
    $isler2026 = \App\Models\TumIsler::whereYear('is_guncellenme_tarihi', 2026)
        ->where('tipi', 'Kazanıldı')
        ->get();
    $toplamTeklif2026 = $isler2026->sum('teklif');
    $kar2026 = $isler2026->sum('kar');
    $karOrani2026 = $toplamTeklif2026 > 0 ? ($kar2026 / $toplamTeklif2026 * 100) : 0;
    
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
            <p class="text-gray-600 text-sm font-semibold uppercase">2025 Kar</p>
            <p class="text-2xl font-bold text-purple-600">{{ number_format($kar2025, 0, ',', '.') }} ₺</p>
            <p class="text-xs text-gray-500 mt-1">%{{ number_format($karOrani2025, 1, ',', '.') }} mar.</p>
        </div>

        <!-- 2026 Karlılık -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
            <p class="text-gray-600 text-sm font-semibold uppercase">2026 Kar</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($kar2026, 0, ',', '.') }} ₺</p>
            <p class="text-xs text-gray-500 mt-1">%{{ number_format($karOrani2026, 1, ',', '.') }} mar.</p>
        </div>

        <!-- Yıllık Karşılaştırma -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <p class="text-gray-600 text-sm font-semibold uppercase">Kar Farkı</p>
            @php $karFarki = $kar2026 - $kar2025; @endphp
            <p class="text-2xl font-bold {{ $karFarki >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $karFarki >= 0 ? '+' : '' }}{{ number_format($karFarki, 0, ',', '.') }} ₺
            </p>
        </div>
    </div>

    <!-- Widget Sistemi (Notion-like) -->
    <div class="mt-12">
        <livewire:dashboard-manager />
    </div>
</flux:main>
</x-layouts::app.sidebar>
