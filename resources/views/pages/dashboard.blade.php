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
    $toplamKazanilanIsler = \App\Models\TumIsler::where('tipi', 'Kazanıldı')->count();
    $toplamTeklifAsamasinda = \App\Models\TumIsler::where('tipi', 'Teklif Aşamasında')->count();
@endphp

<div class="container mx-auto px-4 py-8">
    <!-- Üst Özet Kartları -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Toplam Müşteri -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium opacity-90">Toplam Müşteri</p>
                    <p class="text-4xl font-bold mt-2">{{ $toplamMusteri }}</p>
                </div>
                <div class="text-5xl opacity-20">👥</div>
            </div>
        </div>

        <!-- Toplam İş -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium opacity-90">Toplam İş</p>
                    <p class="text-4xl font-bold mt-2">{{ $toplamIsler }}</p>
                </div>
                <div class="text-5xl opacity-20">📊</div>
            </div>
        </div>

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