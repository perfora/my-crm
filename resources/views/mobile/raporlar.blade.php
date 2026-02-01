<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Raporlar - Mobil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .widget-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .widget-content.open {
            max-height: 2000px;
            transition: max-height 0.5s ease-in;
        }
        .toggle-icon {
            transition: transform 0.3s ease;
        }
        .toggle-icon.open {
            transform: rotate(180deg);
        }
    </style>
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
                $isler2026 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
                    ->whereYear('kapanis_tarihi', 2026)
                    ->get();
                $buYilKazanilan = $isler2026->sum('teklif_tutari');
                $buYilAlis = $isler2026->sum('alis_tutari');
                $buYilKar = $buYilKazanilan - $buYilAlis;
                $buYilKarOran = $buYilKazanilan > 0 ? ($buYilKar / $buYilKazanilan) * 100 : 0;
                
                // 2026 Kazanılan İşler
                $kazanilan2026 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
                    ->whereYear('kapanis_tarihi', 2026)
                    ->orderBy('kapanis_tarihi', 'desc')
                    ->limit(10)
                    ->get();
                
                // Bekleyen İşler (Öncelik 1, Verilecek, 2026)
                $bekleyenIsler = \App\Models\TumIsler::where('oncelik', '1')
                    ->where('tipi', 'Verilecek')
                    ->whereYear('is_guncellenme_tarihi', 2026)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
                
                // Yüksek Potansiyel Müşteriler (Konya, derece 1-2, 60+ gün)
                $yuksekPotansiyel = \App\Models\Musteri::where('sehir', 'Konya')
                    ->whereIn('derece', ['1 -Sık', '2 - Orta'])
                    ->whereIn('turu', ['Resmi Kurum', 'Üniversite', 'Belediye', 'Hastane', 'Özel Sektör'])
                    ->with(['tumIsler', 'ziyaretler'])
                    ->get()
                    ->filter(function($musteri) {
                        $sonZiyaret = $musteri->ziyaretler->max('ziyaret_tarihi');
                        $sonArama = $musteri->ziyaretler->max('arama_tarihi');
                        
                        if ($sonZiyaret && $sonArama) {
                            $sonTarih = max($sonZiyaret, $sonArama);
                        } elseif ($sonZiyaret) {
                            $sonTarih = $sonZiyaret;
                        } elseif ($sonArama) {
                            $sonTarih = $sonArama;
                        } else {
                            return false;
                        }
                        
                        $gunFarki = (int) \Carbon\Carbon::parse($sonTarih)->diffInDays(now());
                        $musteri->gecen_gun = $gunFarki;
                        return $gunFarki > 60;
                    })
                    ->map(function($musteri) {
                        $musteri->toplam_teklif = $musteri->tumIsler->sum('teklif_tutari');
                        $musteri->kazanilan_tutar = $musteri->tumIsler->where('tipi', 'Kazanıldı')->sum('teklif_tutari');
                        return $musteri;
                    })
                    ->filter(function($musteri) {
                        return $musteri->toplam_teklif > 0; // Teklifi olan müşteriler
                    })
                    ->sortByDesc(function($musteri) {
                        // Önce kazanılanları, sonra kazanamayanları - her ikisi de teklif tutarına göre
                        return [$musteri->kazanilan_tutar > 0 ? 1 : 0, $musteri->toplam_teklif];
                    })
                    ->take(10);
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

            <!-- Bu Yıl Kazanılan & Kar -->
            <div class="grid grid-cols-1 gap-4">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <h2 class="text-lg font-semibold mb-2">💰 2026 Kazanılan</h2>
                    <div class="text-4xl font-bold">
                        ${{ number_format($buYilKazanilan, 0, ',', '.') }}
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                    <h2 class="text-lg font-semibold mb-2">📈 2026 Kar</h2>
                    <div class="text-4xl font-bold">
                        ${{ number_format($buYilKar, 0, ',', '.') }}
                    </div>
                    <div class="text-sm mt-2 opacity-90">
                        %{{ number_format($buYilKarOran, 1) }} kar oranı
                    </div>
                </div>
            </div>

            <!-- Bekleyen İşler -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-4 flex justify-between items-center cursor-pointer active:bg-gray-50" onclick="toggleWidget('bekleyen-isler')">
                    <h2 class="text-lg font-bold text-gray-800">⏳ Bekleyen İşler</h2>
                    <span class="toggle-icon text-2xl" id="bekleyen-isler-icon">▼</span>
                </div>
                <div class="widget-content" id="bekleyen-isler-content">
                    <div class="p-4 pt-0 space-y-3">
                    @forelse($bekleyenIsler as $is)
                        <div class="border-l-4 border-red-500 pl-3 py-2">
                            <div class="font-semibold text-gray-800">{{ $is->name }}</div>
                            <div class="text-sm text-gray-600">
                                {{ $is->musteri ? $is->musteri->sirket : '-' }}
                            </div>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded-full font-semibold">
                                    Öncelik {{ $is->oncelik }}
                                </span>
                                @if($is->teklif_tutari)
                                    <span class="text-sm font-bold text-gray-700">
                                        ${{ number_format($is->teklif_tutari, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">Bekleyen iş yok</div>
                    @endforelse
                    </div>
                </div>
            </div>

            <!-- Yüksek Potansiyel Müşteriler -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-4 flex justify-between items-center cursor-pointer active:bg-gray-50" onclick="toggleWidget('yuksek-potansiyel')">
                    <h2 class="text-lg font-bold text-gray-800">🎯 Yüksek Potansiyel</h2>
                    <span class="toggle-icon text-2xl" id="yuksek-potansiyel-icon">▼</span>
                </div>
                <div class="widget-content" id="yuksek-potansiyel-content">
                    <div class="p-4 pt-0">
                        <div class="text-xs text-gray-500 mb-3">Konya, 60+ gün ziyaret edilmemiş</div>
                        <div class="space-y-3">
                    @forelse($yuksekPotansiyel as $musteri)
                        <div class="border-l-4 border-orange-500 pl-3 py-2">
                            <div class="font-semibold text-gray-800">{{ $musteri->sirket }}</div>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-xs text-red-600">
                                    {{ $musteri->gecen_gun }} gün önce
                                </span>
                                @if($musteri->toplam_teklif > 0)
                                    <span class="text-sm font-bold text-orange-600">
                                        ${{ number_format($musteri->toplam_teklif, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $musteri->derece }} • {{ $musteri->turu }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">Potansiyel müşteri yok</div>
                    @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bekleyen & Planlanan Ziyaretler -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-4 flex justify-between items-center cursor-pointer active:bg-gray-50" onclick="toggleWidget('bekleyen-ziyaretler')">
                    <h2 class="text-lg font-bold text-gray-800">📅 Bekleyen Ziyaretler</h2>
                    <span class="toggle-icon text-2xl" id="bekleyen-ziyaretler-icon">▼</span>
                </div>
                <div class="widget-content" id="bekleyen-ziyaretler-content">
                    <div class="p-4 pt-0">
                        <div class="text-xs text-gray-500 mb-3">Beklemede ve Planlandı durumunda</div>
                        <div class="space-y-3">
                    @forelse(\App\Models\Ziyaret::whereIn('durumu', ['Beklemede', 'Planlandı'])->with('musteri')->orderBy('ziyaret_tarihi', 'asc')->limit(10)->get() as $ziyaret)
                        <div class="border-l-4 border-purple-500 pl-3 py-2">
                            <div class="font-semibold text-gray-800">
                                {{ $ziyaret->musteri ? $ziyaret->musteri->sirket : '-' }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($ziyaret->ziyaret_tarihi)->format('d.m.Y') }}
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs px-2 py-1 {{ $ziyaret->durumu == 'Beklemede' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }} rounded-full font-semibold">
                                    {{ $ziyaret->durumu }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">Bekleyen veya planlanan ziyaret yok</div>
                    @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleWidget(widgetId) {
            const content = document.getElementById(widgetId + '-content');
            const icon = document.getElementById(widgetId + '-icon');
            
            content.classList.toggle('open');
            icon.classList.toggle('open');
        }
    </script>
</body>
</html>
