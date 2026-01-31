<!-- Yaklaşan Ziyaretler Widget -->
@php
    $yaklasanZiyaretler = \App\Models\Ziyaret::whereIn('durumu', ['Planlandı', 'Beklemede'])
        ->where('ziyaret_tarihi', '>=', now())
        ->orderBy('ziyaret_tarihi', 'asc')
        ->with('musteri')
        ->limit(10)
        ->get();
    
    $columns = [
        ['label' => 'Ziyaret', 'field' => 'ziyaret_ismi'],
        ['label' => 'Müşteri', 'format' => function($item) {
            if($item->musteri) {
                return '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">' . $item->musteri->sirket . '</span>';
            }
            return '-';
        }],
        ['label' => 'Durum', 'format' => function($item) {
            $renkler = [
                'Planlandı' => 'bg-blue-100 text-blue-800',
                'Beklemede' => 'bg-yellow-100 text-yellow-800'
            ];
            $renk = $renkler[$item->durumu] ?? 'bg-gray-100 text-gray-800';
            return '<span class="px-2 py-1 text-xs rounded-full ' . $renk . '">' . $item->durumu . '</span>';
        }],
        ['label' => 'Tarih', 'format' => function($item) {
            $tarih = \Carbon\Carbon::parse($item->ziyaret_tarihi);
            $gunFarki = now()->diffInDays($tarih, false);
            $renk = $gunFarki <= 3 ? 'text-red-600 font-bold' : 'text-gray-600';
            return '<span class="' . $renk . '">' . $tarih->format('d.m.Y H:i') . '</span>';
        }],
        ['label' => 'Tür', 'format' => function($item) {
            return '<span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">' . ($item->tur ?? '-') . '</span>';
        }],
    ];
@endphp

<x-dashboard-widget title="📅 Yaklaşan Ziyaretler" noPadding="true">
    <x-data-table :items="$yaklasanZiyaretler" :columns="$columns" emptyMessage="Planlanmış ziyaret yok" />
</x-dashboard-widget>
