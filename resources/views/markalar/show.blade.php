<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $marka->name }} - Marka Detay</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    @include('layouts.nav')
    
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <a href="/markalar" class="text-blue-600 hover:underline">← Markalar</a>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold">{{ $marka->name }}</h1>
                <a href="/markalar/{{ $marka->id }}/edit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    ✏️ Düzenle
                </a>
            </div>
        </div>

        @php
            // İstatistikler
            $tumIsler = \App\Models\TumIsler::where('marka_id', $marka->id)->get();
            $toplamIs = $tumIsler->count();
            $kazanilanIsler = $tumIsler->where('tipi', 'Kazanıldı');
            $kazanilanAdet = $kazanilanIsler->count();
            
            // Para hesaplamaları (USD'ye çevir)
            $toplamTeklif = 0;
            $toplamAlis = 0;
            foreach($kazanilanIsler as $is) {
                $teklifUSD = $is->teklif_doviz == 'TL' ? ($is->teklif_tutari / 35) : $is->teklif_tutari;
                $alisUSD = $is->alis_doviz == 'TL' ? ($is->alis_tutari / 35) : $is->alis_tutari;
                $toplamTeklif += $teklifUSD;
                $toplamAlis += $alisUSD;
            }
            $toplamKar = $toplamTeklif - $toplamAlis;
            $karOrani = $toplamAlis > 0 ? (($toplamKar / $toplamAlis) * 100) : 0;
            
            // Müşteri bazında grupla
            $musteriGrup = $kazanilanIsler->groupBy('musteri_id')->map(function($isler) {
                $musteri = $isler->first()->musteri;
                $toplamTeklif = 0;
                $toplamAlis = 0;
                
                foreach($isler as $is) {
                    $teklifUSD = $is->teklif_doviz == 'TL' ? ($is->teklif_tutari / 35) : $is->teklif_tutari;
                    $alisUSD = $is->alis_doviz == 'TL' ? ($is->alis_tutari / 35) : $is->alis_tutari;
                    $toplamTeklif += $teklifUSD;
                    $toplamAlis += $alisUSD;
                }
                
                return [
                    'musteri' => $musteri,
                    'adet' => $isler->count(),
                    'toplam_teklif' => $toplamTeklif,
                    'toplam_kar' => $toplamTeklif - $toplamAlis,
                ];
            })->sortByDesc('toplam_kar');
            
            // Yıl bazında grupla
            $yilGrup = $kazanilanIsler->groupBy(function($is) {
                return $is->kapanis_tarihi ? date('Y', strtotime($is->kapanis_tarihi)) : 'Bilinmiyor';
            })->map(function($isler) {
                $toplamTeklif = 0;
                $toplamAlis = 0;
                
                foreach($isler as $is) {
                    $teklifUSD = $is->teklif_doviz == 'TL' ? ($is->teklif_tutari / 35) : $is->teklif_tutari;
                    $alisUSD = $is->alis_doviz == 'TL' ? ($is->alis_tutari / 35) : $is->alis_tutari;
                    $toplamTeklif += $teklifUSD;
                    $toplamAlis += $alisUSD;
                }
                
                return [
                    'adet' => $isler->count(),
                    'toplam_teklif' => $toplamTeklif,
                    'toplam_kar' => $toplamTeklif - $toplamAlis,
                ];
            })->sortKeys();
        @endphp

        <!-- İstatistik Kartları -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Toplam İş</div>
                <div class="text-3xl font-bold">{{ $toplamIs }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Kazanılan</div>
                <div class="text-3xl font-bold text-green-600">{{ $kazanilanAdet }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Toplam Ciro</div>
                <div class="text-2xl font-bold text-blue-600">${{ number_format($toplamTeklif, 2) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Toplam Kar</div>
                <div class="text-2xl font-bold text-green-600">${{ number_format($toplamKar, 2) }}</div>
                <div class="text-sm text-gray-500">%{{ number_format($karOrani, 1) }}</div>
            </div>
        </div>

        <!-- Müşteri Bazında Analiz -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold">👥 Müşteri Bazında Satışlar</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Müşteri</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">İş Adedi</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Toplam Satış</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Toplam Kar</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($musteriGrup as $data)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium">
                                    @if($data['musteri'])
                                        <a href="/musteriler/{{ $data['musteri']->id }}" class="text-blue-600 hover:underline">
                                            {{ $data['musteri']->sirket }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-semibold">{{ $data['adet'] }}</td>
                                <td class="px-6 py-4 text-right">${{ number_format($data['toplam_teklif'], 2) }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-green-600">${{ number_format($data['toplam_kar'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    Henüz kazanılmış iş bulunmuyor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Yıl Bazında Analiz -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold">📅 Yıllara Göre Satışlar</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Yıl</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">İş Adedi</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Toplam Satış</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Toplam Kar</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($yilGrup as $yil => $data)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium">{{ $yil }}</td>
                                <td class="px-6 py-4 text-right font-semibold">{{ $data['adet'] }}</td>
                                <td class="px-6 py-4 text-right">${{ number_format($data['toplam_teklif'], 2) }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-green-600">${{ number_format($data['toplam_kar'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    Henüz kazanılmış iş bulunmuyor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tüm İşler Listesi -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold">📋 Tüm İşler</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İş Adı</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Müşteri</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipi</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Teklif</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Kar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tumIsler->sortByDesc('is_guncellenme_tarihi') as $is)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="/tum-isler/{{ $is->id }}/edit" class="text-blue-600 hover:underline">
                                        {{ $is->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    @if($is->musteri)
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ $is->musteri->sirket }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($is->tipi == 'Kazanıldı') bg-green-100 text-green-800
                                        @elseif($is->tipi == 'Verilecek') bg-yellow-100 text-yellow-800
                                        @elseif($is->tipi == 'Kaybedildi') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $is->tipi }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($is->teklif_tutari)
                                        {{ number_format($is->teklif_tutari, 2) }} {{ $is->teklif_doviz }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-green-600">
                                    @if($is->tipi == 'Kazanıldı' && $is->teklif_tutari && $is->alis_tutari)
                                        @php
                                            $teklifUSD = $is->teklif_doviz == 'TL' ? ($is->teklif_tutari / 35) : $is->teklif_tutari;
                                            $alisUSD = $is->alis_doviz == 'TL' ? ($is->alis_tutari / 35) : $is->alis_tutari;
                                            $kar = $teklifUSD - $alisUSD;
                                        @endphp
                                        ${{ number_format($kar, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $is->is_guncellenme_tarihi ? date('d.m.Y', strtotime($is->is_guncellenme_tarihi)) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    Bu marka için henüz iş kaydı bulunmuyor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
