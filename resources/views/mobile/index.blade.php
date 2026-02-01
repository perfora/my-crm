<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CRM Mobil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        .mobile-btn {
            min-height: 80px;
            font-size: 1.25rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-blue-600 text-white p-6 shadow-lg">
            <h1 class="text-2xl font-bold">CRM Mobil</h1>
            <p class="text-blue-100 text-sm mt-1">Hoş geldin, {{ auth()->user()->name }}</p>
        </div>

        <!-- Ana Butonlar -->
        <div class="p-6 space-y-4">
            <a href="/mobile/yeni-is" class="mobile-btn block bg-green-500 hover:bg-green-600 text-white rounded-xl shadow-lg active:scale-95 transition-transform flex items-center justify-center gap-3">
                <span class="text-4xl">➕</span>
                <span>Yeni İş</span>
            </a>

            <a href="/mobile/yeni-ziyaret" class="mobile-btn block bg-purple-500 hover:bg-purple-600 text-white rounded-xl shadow-lg active:scale-95 transition-transform flex items-center justify-center gap-3">
                <span class="text-4xl">📍</span>
                <span>Yeni Ziyaret</span>
            </a>

            <a href="/mobile/raporlar" class="mobile-btn block bg-orange-500 hover:bg-orange-600 text-white rounded-xl shadow-lg active:scale-95 transition-transform flex items-center justify-center gap-3">
                <span class="text-4xl">📊</span>
                <span>Raporlar</span>
            </a>

            <a href="/" class="mobile-btn block bg-gray-500 hover:bg-gray-600 text-white rounded-xl shadow-lg active:scale-95 transition-transform flex items-center justify-center gap-3">
                <span class="text-4xl">💻</span>
                <span>Masaüstü Site</span>
            </a>
        </div>

        <!-- Hızlı İstatistikler -->
        <div class="p-6">
            <h2 class="text-lg font-bold text-gray-700 mb-4">Bugün</h2>
            <div class="grid grid-cols-2 gap-4">
                @php
                    $bugunIsler = \App\Models\TumIsler::whereDate('created_at', today())->count();
                    $bugunZiyaretler = \App\Models\Ziyaret::whereDate('ziyaret_tarihi', today())->count();
                @endphp
                
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $bugunIsler }}</div>
                    <div class="text-sm text-gray-600 mt-1">Yeni İş</div>
                </div>

                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-3xl font-bold text-purple-600">{{ $bugunZiyaretler }}</div>
                    <div class="text-sm text-gray-600 mt-1">Ziyaret</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
