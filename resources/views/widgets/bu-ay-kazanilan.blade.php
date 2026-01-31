<!-- Bu Ayki Kazanılan İşler Widget -->
@php
    $buAyKazanilan = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
        ->whereYear('kapanis_tarihi', date('Y'))
        ->whereMonth('kapanis_tarihi', date('m'))
        ->with('musteri')
        ->orderBy('kapanis_tarihi', 'desc')
        ->get();
    
    $toplamTeklif = 0;
    $toplamAlis = 0;
    foreach($buAyKazanilan as $is) {
        if($is->teklif_doviz === 'USD' && $is->teklif_tutari) {
            $toplamTeklif += $is->teklif_tutari;
        }
        if($is->alis_doviz === 'USD' && $is->alis_tutari) {
            $toplamAlis += $is->alis_tutari;
        }
    }
    
    $toplamKar = $toplamTeklif - $toplamAlis;
    $karOrani = $toplamTeklif > 0 ? ($toplamKar / $toplamTeklif * 100) : 0;
    
    $columns = [
        ['label' => 'İş Adı', 'field' => 'name'],
        ['label' => 'Müşteri', 'format' => function($item) {
            if($item->musteri) {
                return '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">' . $item->musteri->sirket . '</span>';
            }
            return '-';
        }],
        ['label' => 'Teklif', 'format' => function($item) {
            if($item->teklif_tutari && $item->teklif_doviz === 'USD') {
                return '$' . number_format($item->teklif_tutari, 2);
            }
            return '-';
        }],
        ['label' => 'Kar', 'format' => function($item) {
            if($item->teklif_tutari && $item->alis_tutari && $item->teklif_doviz === 'USD' && $item->alis_doviz === 'USD') {
                $kar = $item->teklif_tutari - $item->alis_tutari;
                $oran = $item->teklif_tutari > 0 ? ($kar / $item->teklif_tutari * 100) : 0;
                $renk = $kar >= 0 ? 'text-green-600' : 'text-red-600';
                return '<span class="' . $renk . ' font-semibold">$' . number_format($kar, 2) . ' <span class="text-xs">(' . number_format($oran, 1) . '%)</span></span>';
            }
            return '-';
        }],
        ['label' => 'Kapanış', 'format' => function($item) {
            return $item->kapanis_tarihi ? \Carbon\Carbon::parse($item->kapanis_tarihi)->format('d.m.Y') : '-';
        }],
    ];
@endphp

<x-dashboard-widget title="{{ date('F Y') }} - Kazanılan İşler" noPadding="true">
    <x-slot name="action">
        <div class="text-right space-y-1">
            <div>
                <span class="text-sm text-gray-600">Toplam Teklif: </span>
                <span class="text-lg font-bold text-blue-600">${{ number_format($toplamTeklif, 2) }}</span>
            </div>
            <div>
                <span class="text-sm text-gray-600">Toplam Kar: </span>
                <span class="text-lg font-bold text-green-600">${{ number_format($toplamKar, 2) }}</span>
                <span class="text-sm text-gray-600">({{ number_format($karOrani, 1) }}%)</span>
            </div>
        </div>
    </x-slot>
    
    <x-data-table :items="$buAyKazanilan" :columns="$columns" emptyMessage="Bu ay henüz kazanılan iş yok" />
</x-dashboard-widget>
