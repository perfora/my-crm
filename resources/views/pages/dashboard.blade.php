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
    
    // Sabit Widget Verileri
    $teklifAsamasindakiler = \App\Models\TumIsler::where('tipi', 'Teklif Aşamasında')->orderBy('id', 'desc')->limit(10)->get();
    $devamEdecekler = \App\Models\TumIsler::where('tipi', 'Devam Edecek')->orderBy('id', 'desc')->limit(10)->get();
    $kazanilanlar2025 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')->whereYear('kapanis_tarihi', 2025)->orderBy('kapanis_tarihi', 'desc')->limit(10)->get();
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

    <!-- Sabit Widget'lar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
        <!-- Teklif Aşamasındakiler -->
        <div class="bg-white rounded-lg shadow-md border-t-4 border-yellow-500">
            <div class="p-4 border-b bg-yellow-50">
                <h3 class="text-lg font-bold text-yellow-800">📋 Teklif Aşamasındakiler</h3>
                <p class="text-xs text-gray-600">Son 10 kayıt</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">İş Adı</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Müşteri</th>
                            <th class="px-3 py-2 text-right font-semibold text-gray-700">Teklif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teklifAsamasindakiler as $is)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-3 py-2 text-left">{{ $is->name }}</td>
                            <td class="px-3 py-2 text-left">{{ $is->musteri->sirket ?? '-' }}</td>
                            <td class="px-3 py-2 text-right font-mono">${{ number_format($is->teklif_tutari, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Devam Edecekler -->
        <div class="bg-white rounded-lg shadow-md border-t-4 border-blue-500">
            <div class="p-4 border-b bg-blue-50">
                <h3 class="text-lg font-bold text-blue-800">🔄 Devam Edecekler</h3>
                <p class="text-xs text-gray-600">Son 10 kayıt</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">İş Adı</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Müşteri</th>
                            <th class="px-3 py-2 text-right font-semibold text-gray-700">Teklif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devamEdecekler as $is)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-3 py-2 text-left">{{ $is->name }}</td>
                            <td class="px-3 py-2 text-left">{{ $is->musteri->sirket ?? '-' }}</td>
                            <td class="px-3 py-2 text-right font-mono">${{ number_format($is->teklif_tutari, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2025 Kazanılanlar -->
        <div class="bg-white rounded-lg shadow-md border-t-4 border-green-500">
            <div class="p-4 border-b bg-green-50">
                <h3 class="text-lg font-bold text-green-800">✅ 2025 Kazanılanlar</h3>
                <p class="text-xs text-gray-600">Son 10 kayıt</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">İş Adı</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Müşteri</th>
                            <th class="px-3 py-2 text-right font-semibold text-gray-700">Teklif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kazanilanlar2025 as $is)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-3 py-2 text-left">{{ $is->name }}</td>
                            <td class="px-3 py-2 text-left">{{ $is->musteri->sirket ?? '-' }}</td>
                            <td class="px-3 py-2 text-right font-mono">${{ number_format($is->teklif_tutari, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</flux:main>
</x-layouts::app.sidebar>
