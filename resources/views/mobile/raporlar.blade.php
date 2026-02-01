<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Raporlar - Mobil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-orange-600 text-white p-4 shadow-lg flex items-center gap-3">
            <a href="/mobile" class="text-3xl">←</a>
            <h1 class="text-xl font-bold">Raporlar</h1>
        </div>

        <div class="p-6 space-y-6">
            @php
                // Bu Ay
                $buAyIsler = \App\Models\TumIsler::whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'))
                    ->count();
                $buAyKazanilan = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
                    ->whereMonth('kapanis_tarihi', date('m'))
                    ->whereYear('kapanis_tarihi', date('Y'))
                    ->count();
                $buAyZiyaret = \App\Models\Ziyaret::whereMonth('ziyaret_tarihi', date('m'))
                    ->whereYear('ziyaret_tarihi', date('Y'))
                    ->count();
                
                // Bu Yıl
                $buYilKazanilan = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
                    ->whereYear('kapanis_tarihi', date('Y'))
                    ->sum('teklif_tutari');
            @endphp

            <!-- Bu Ay -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📅 Bu Ay</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Yeni İş</span>
                        <span class="text-2xl font-bold text-green-600">{{ $buAyIsler }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Kazanılan</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $buAyKazanilan }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600">Ziyaret</span>
                        <span class="text-2xl font-bold text-purple-600">{{ $buAyZiyaret }}</span>
                    </div>
                </div>
            </div>

            <!-- Bu Yıl Kazanılan -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                <h2 class="text-lg font-semibold mb-2">💰 {{ date('Y') }} Kazanılan Tutar</h2>
                <div class="text-4xl font-bold">
                    {{ number_format($buYilKazanilan, 0, ',', '.') }} ₺
                </div>
            </div>

            <!-- Son İşler -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📋 Son İşler</h2>
                <div class="space-y-3">
                    @foreach(\App\Models\TumIsler::with('musteri')->orderBy('created_at', 'desc')->limit(5)->get() as $is)
                        <div class="border-l-4 border-green-500 pl-3 py-2">
                            <div class="font-semibold text-gray-800">{{ $is->name }}</div>
                            <div class="text-sm text-gray-600">
                                {{ $is->musteri ? $is->musteri->sirket : '-' }}
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                {{ $is->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Son Ziyaretler -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">🚗 Son Ziyaretler</h2>
                <div class="space-y-3">
                    @foreach(\App\Models\Ziyaret::with('musteri')->orderBy('ziyaret_tarihi', 'desc')->limit(5)->get() as $ziyaret)
                        <div class="border-l-4 border-purple-500 pl-3 py-2">
                            <div class="font-semibold text-gray-800">
                                {{ $ziyaret->musteri ? $ziyaret->musteri->sirket : '-' }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($ziyaret->ziyaret_tarihi)->format('d.m.Y') }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $ziyaret->durumu }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</body>
</html>
