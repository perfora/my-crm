<!-- Bekleyen İşler Widget -->
@php
    $bekleyenIsler = \App\Models\TumIsler::where('tipi', 'Verilecek')
        ->with('musteri')
        ->orderBy('oncelik', 'asc')
        ->orderBy('is_guncellenme_tarihi', 'desc')
        ->limit(10)
        ->get();
    
    $columns = [
        ['label' => 'Öncelik', 'format' => function($item) {
            $colors = [
                '1' => 'bg-red-100 text-red-800',
                '2' => 'bg-orange-100 text-orange-800',
                '3' => 'bg-yellow-100 text-yellow-800',
                '4' => 'bg-green-100 text-green-800',
            ];
            $color = $colors[$item->oncelik ?? '4'] ?? 'bg-gray-100 text-gray-800';
            return '<span class="px-2 py-1 text-xs rounded-full font-bold ' . $color . '">' . ($item->oncelik ?? '-') . '</span>';
        }],
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
        ['label' => 'Açılış', 'format' => function($item) {
            return $item->is_guncellenme_tarihi ? \Carbon\Carbon::parse($item->is_guncellenme_tarihi)->format('d.m.Y') : '-';
        }],
    ];
@endphp

<x-dashboard-widget title="Verilecek İşler (Önceliğe Göre)" noPadding="true">
    <x-data-table :items="$bekleyenIsler" :columns="$columns" />
</x-dashboard-widget>