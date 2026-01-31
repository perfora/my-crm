<!-- Yüksek Öncelikli İşler Widget -->
@php
    $oncelikliIsler = \App\Models\TumIsler::where('oncelik', '1')
        ->whereNotIn('tipi', ['Kazanıldı', 'Kaybedildi'])
        ->with('musteri')
        ->orderBy('kapanis_tarihi', 'asc')
        ->limit(10)
        ->get();
    
    $columns = [
        ['label' => 'İş Adı', 'field' => 'name'],
        ['label' => 'Müşteri', 'format' => function($item) {
            if($item->musteri) {
                return '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">' . $item->musteri->sirket . '</span>';
            }
            return '-';
        }],
        ['label' => 'Tipi', 'format' => function($item) {
            return '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">' . ($item->tipi ?? '-') . '</span>';
        }],
        ['label' => 'Öncelik', 'format' => function($item) {
            return '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">🔥 ' . $item->oncelik . '</span>';
        }],
        ['label' => 'Kapanış', 'format' => function($item) {
            return $item->kapanis_tarihi ? \Carbon\Carbon::parse($item->kapanis_tarihi)->format('d.m.Y') : '-';
        }],
    ];
@endphp

<x-dashboard-widget title="🔥 Yüksek Öncelikli İşler" noPadding="true">
    <x-data-table :items="$oncelikliIsler" :columns="$columns" emptyMessage="Yüksek öncelikli iş yok" />
</x-dashboard-widget>
