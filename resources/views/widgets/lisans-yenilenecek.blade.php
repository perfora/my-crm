<!-- Lisans Yenilenecek İşler Widget -->
<!-- DEBUG: Widget yükleniyor -->
@php
    try {
        $bugun = \Carbon\Carbon::now();
        $ucAySonra = $bugun->copy()->addMonths(3);
    
    // Geçmiş yıllarda kazanılan, önümüzdeki 3 ayda lisansı bitecek TÜM işler (açılmış olsa da)
    $yenilenecekler = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
        ->whereNotNull('lisans_bitis')
        ->whereYear('kapanis_tarihi', '<', 2026)
        ->whereBetween('lisans_bitis', [$bugun, $ucAySonra])
        ->with(['musteri', 'marka'])
        ->orderBy('lisans_bitis', 'asc')
        ->get();
    
    // İstatistikler
    $buAyBitenler = $yenilenecekler->filter(function($is) use ($bugun) {
        return \Carbon\Carbon::parse($is->lisans_bitis)->month == $bugun->month;
    })->count();
    
    $ucAyIcindeBitenler = $yenilenecekler->filter(function($is) use ($bugun) {
        return \Carbon\Carbon::parse($is->lisans_bitis)->diffInDays($bugun, false) >= -90;
    })->count();
    
    // Henüz açılmayanları say
    $acilmamis = $yenilenecekler->filter(function($is) {
        $yenilemeVarMi = \App\Models\TumIsler::where('musteri_id', $is->musteri_id)
            ->where('marka_id', $is->marka_id)
            ->where('lisans_bitis', $is->lisans_bitis)
            ->whereYear('is_guncellenme_tarihi', 2026)
            ->whereIn('tipi', ['Verilecek', 'Verildi', 'Takip Edilecek'])
            ->exists();
        return !$yenilemeVarMi;
    })->count();
    
    $toplamPotansiyel = 0;
    foreach($yenilenecekler as $is) {
        if($is->teklif_doviz === 'USD' && $is->teklif_tutari) {
            $toplamPotansiyel += $is->teklif_tutari;
        }
    }
    } catch (\Exception $e) {
        $widgetHata = $e->getMessage();
        $yenilenecekler = collect([]);
        $acilmamis = 0;
        $buAyBitenler = 0;
        $toplamPotansiyel = 0;
    }
@endphp

@if(isset($widgetHata))
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    <strong>Widget Hatası:</strong> {{ $widgetHata }}
</div>
@endif

<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">🔄 Lisans Yenilenecek İşler</h2>
            <div class="text-sm text-gray-600">
                <span class="font-semibold">{{ $yenilenecekler->count() }}</span> toplam
                @if($acilmamis > 0)
                    <span class="text-orange-600 font-semibold ml-2">({{ $acilmamis }} bekliyor)</span>
                @endif
            </div>
        </div>
        
        <!-- İstatistikler -->
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="bg-orange-50 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $acilmamis }}</div>
                <div class="text-xs text-gray-600">Açılmamış</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $buAyBitenler }}</div>
                <div class="text-xs text-gray-600">Bu Ay Bitenler</div>
            </div>
            <div class="bg-green-50 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-green-600">${{ number_format($toplamPotansiyel, 0) }}</div>
                <div class="text-xs text-gray-600">Potansiyel Gelir</div>
            </div>
        </div>
    </div>
    
    <!-- Liste -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Müşteri</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">İş</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marka</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lisans Bitiş</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kalan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Son Teklif</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($yenilenecekler as $is)
                    @php
                        $bitisTarihi = \Carbon\Carbon::parse($is->lisans_bitis);
                        $kalanGun = (int)$bugun->diffInDays($bitisTarihi, false);
                        
                        if($kalanGun < 0) {
                            $kalanText = abs($kalanGun) . ' gün önce';
                            $kalanColor = 'bg-red-700 text-white';
                        } elseif($kalanGun < 30) {
                            $kalanText = $kalanGun . ' gün';
                            $kalanColor = 'bg-red-100 text-red-800';
                        } elseif($kalanGun < 90) {
                            $kalanText = $kalanGun . ' gün';
                            $kalanColor = 'bg-yellow-100 text-yellow-800';
                        } else {
                            $kalanAy = (int)round($kalanGun / 30);
                            $kalanText = $kalanAy . ' ay';
                            $kalanColor = 'bg-green-100 text-green-800';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $is->musteri ? $is->musteri->sirket : '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $is->name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                {{ $is->marka ? $is->marka->name : '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $bitisTarihi->format('d.m.Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full font-semibold {{ $kalanColor }}">
                                {{ $kalanText }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold">
                            @if($is->teklif_tutari && $is->teklif_doviz === 'USD')
                                ${{ number_format($is->teklif_tutari, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                // Aynı müşteri + marka + lisans_bitis için 2026'da yenileme var mı?
                                $yenilemeVarMi = \App\Models\TumIsler::where('musteri_id', $is->musteri_id)
                                    ->where('marka_id', $is->marka_id)
                                    ->where('lisans_bitis', $is->lisans_bitis)
                                    ->whereYear('is_guncellenme_tarihi', 2026)
                                    ->whereIn('tipi', ['Verilecek', 'Verildi', 'Takip Edilecek'])
                                    ->exists();
                            @endphp
                            @if(!$yenilemeVarMi)
                                <button 
                                    onclick="yenilemeAc({{ $is->id }})"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium transition">
                                    🔄 Yenile
                                </button>
                            @else
                                <span class="text-green-600 text-xs font-semibold">✓ Açıldı</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            🎉 Harika! Tüm lisanslar için yenileme kaydı açılmış.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function yenilemeAc(isId) {
    if(!confirm('Bu iş için yenileme kaydı açılsın mı?')) {
        return;
    }
    
    const button = event.target;
    button.disabled = true;
    button.innerHTML = '⏳ Açılıyor...';
    
    $.ajax({
        url: '/api/yenileme-ac',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: { is_id: isId },
        success: function(response) {
            alert('✓ Yenileme kaydı oluşturuldu!\n\nYeni iş: ' + response.yeni_is.name);
            // Butonu "✓ Açıldı" yap
            $(button).closest('td').html('<span class="text-green-600 text-xs font-semibold">✓ Açıldı</span>');
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.message || 'Hata oluştu!';
            alert('❌ ' + error);
            button.disabled = false;
            button.innerHTML = '📝 Yenileme Aç';
        }
    });
}
</script>
