<!-- Uzun Süredir Ziyaret Edilmeyen Müşteriler Widget -->
@php
    $bugun = \Carbon\Carbon::now();
    
    // Derece 1, 2, 3 olan müşteriler (text olarak saklanıyor: "1 -Sık", "2 - Orta", "3- Düşük")
    $oncelikliMusteriler = \App\Models\Musteri::where(function($query) {
            $query->where('derece', 'LIKE', '1%')
                  ->orWhere('derece', 'LIKE', '2%')
                  ->orWhere('derece', 'LIKE', '3%');
        })
        ->get();
    
    $musteriZiyaretler = [];
    
    foreach($oncelikliMusteriler as $musteri) {
        $sonZiyaret = \App\Models\Ziyaret::where('musteri_id', $musteri->id)
            ->where('durumu', 'Tamamlandı')
            ->orderBy('ziyaret_tarihi', 'desc')
            ->first();
        
        $sonZiyaretTarihi = $sonZiyaret ? \Carbon\Carbon::parse($sonZiyaret->ziyaret_tarihi) : null;
        $gecenGun = $sonZiyaretTarihi ? $sonZiyaretTarihi->diffInDays($bugun) : 999;
        
        // Derece değerini parse et (ilk karakteri al: "1 -Sık" -> 1)
        $dereceNumara = (int)substr($musteri->derece, 0, 1);
        
        // Derece bazında renk ve aciliyet
        if($dereceNumara == 1) {
            $uyariGun = 30; // 1. derece için 30 gün
        } elseif($dereceNumara == 2) {
            $uyariGun = 60; // 2. derece için 60 gün
        } else {
            $uyariGun = 90; // 3. derece için 90 gün
        }
        
        $musteriZiyaretler[] = [
            'musteri' => $musteri,
            'derece_numara' => $dereceNumara,
            'son_ziyaret' => $sonZiyaretTarihi,
            'gecen_gun' => $gecenGun,
            'uyari_gun' => $uyariGun,
        ];
    }
    
    // Önce derece sonra geçen güne göre sırala (1. derece en üstte, içinde en eskiler önce)
    usort($musteriZiyaretler, function($a, $b) {
        if ($a['derece_numara'] == $b['derece_numara']) {
            return $b['gecen_gun'] - $a['gecen_gun']; // Aynı dereceyse en eski önce
        }
        return $a['derece_numara'] - $b['derece_numara']; // Derece küçük olan önce (1, 2, 3)
    });
    
    // Sadece ilk 10'u al
    $musteriZiyaretler = array_slice($musteriZiyaretler, 0, 10);
@endphp

<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h2 class="text-xl font-bold">⏰ Uzun Süredir Ziyaret Edilmeyen Müşteriler</h2>
        <p class="text-sm text-gray-600 mt-1">Derece 1, 2, 3 müşteriler - en uzun süre geçenler</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Derece</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Müşteri</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Şehir</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Son Ziyaret</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Geçen Süre</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Durum</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($musteriZiyaretler as $item)
                    @php
                        $musteri = $item['musteri'];
                        $dereceNumara = $item['derece_numara'];
                        $sonZiyaret = $item['son_ziyaret'];
                        $gecenGun = $item['gecen_gun'];
                        $uyariGun = $item['uyari_gun'];
                        
                        // Derece rengi
                        $dereceRenk = [
                            1 => 'bg-red-100 text-red-800 font-bold',
                            2 => 'bg-orange-100 text-orange-800',
                            3 => 'bg-yellow-100 text-yellow-800',
                        ];
                        $dereceClass = $dereceRenk[$dereceNumara] ?? 'bg-gray-100 text-gray-800';
                        
                        // Durum rengi
                        if(!$sonZiyaret) {
                            $durumText = 'Hiç ziyaret edilmedi';
                            $durumColor = 'bg-red-700 text-white';
                        } elseif($gecenGun > $uyariGun) {
                            $durumText = 'Acil!';
                            $durumColor = 'bg-red-600 text-white';
                        } elseif($gecenGun > ($uyariGun * 0.7)) {
                            $durumText = 'Dikkat';
                            $durumColor = 'bg-orange-500 text-white';
                        } else {
                            $durumText = 'Normal';
                            $durumColor = 'bg-green-500 text-white';
                        }
                        
                        // Geçen süre metni (kesirli)
                        if(!$sonZiyaret) {
                            $gecenSureText = '-';
                        } elseif($gecenGun < 30) {
                            $gecenSureText = $gecenGun . ' gün';
                        } elseif($gecenGun < 365) {
                            $ay = round($gecenGun / 30, 1);
                            $gecenSureText = $ay . ' ay';
                        } else {
                            $yil = round($gecenGun / 365, 1);
                            $gecenSureText = $yil . ' yıl';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 text-xs rounded-full {{ $dereceClass }}">
                                {{ $dereceNumara }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="/musteriler/{{ $musteri->id }}" class="text-blue-600 hover:underline font-medium">
                                {{ $musteri->sirket }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $musteri->sehir ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $sonZiyaret ? $sonZiyaret->format('d.m.Y') : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm font-semibold {{ $gecenGun > $uyariGun ? 'text-red-600' : 'text-gray-700' }}">
                                {{ $gecenSureText }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-3 py-1 text-xs rounded-full font-semibold {{ $durumColor }}">
                                {{ $durumText }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Derece 1-2-3 müşteri bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
