<!-- 
    ÖRNEK WIDGET ŞABLONU
    
    Bu dosyayı kopyalayıp kendi widget'ını oluşturabilirsin:
    1. Bu dosyayı widgets/kendi-widget-adi.blade.php olarak kaydet
    2. $items sorgusunu kendi ihtiyacına göre değiştir
    3. $columns array'ini istediğin sütunları gösterecek şekilde düzenle
    4. dashboard.blade.php'de @include('widgets.kendi-widget-adi') ile kullan
-->

@php
    // Kendi sorgunuzu buraya yazın
    $items = \App\Models\TumIsler::where('tipi', 'Verildi')
        ->with('musteri')
        ->orderBy('created_at', 'desc')
        ->limit(15)
        ->get();
    
    // Toplam hesaplama örneği (opsiyonel)
    $toplam = $items->sum('teklif_tutari');
    
    // Sütun tanımlamaları
    $columns = [
        // Basit alan gösterimi
        [
            'label' => 'İş Adı',
            'field' => 'name'
        ],
        
        // Özel formatlama ile
        [
            'label' => 'Müşteri', 
            'format' => function($item) {
                if($item->musteri) {
                    return '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">' 
                           . $item->musteri->sirket . '</span>';
                }
                return '-';
            }
        ],
        
        // Tarih formatı
        [
            'label' => 'Tarih',
            'format' => function($item) {
                return $item->created_at->format('d.m.Y');
            }
        ],
        
        // Para formatı
        [
            'label' => 'Tutar',
            'format' => function($item) {
                if($item->teklif_tutari && $item->teklif_doviz === 'USD') {
                    return '$' . number_format($item->teklif_tutari, 2);
                }
                return '-';
            }
        ],
    ];
@endphp

<x-dashboard-widget title="Widget Başlığı" noPadding="true">
    <!-- Opsiyonel: Başlıkta ekstra bilgi göster -->
    <x-slot name="action">
        <div class="text-right">
            <span class="text-sm text-gray-600">Toplam: </span>
            <span class="text-lg font-bold text-green-600">${{ number_format($toplam, 2) }}</span>
        </div>
    </x-slot>
    
    <!-- Tablo -->
    <x-data-table 
        :items="$items" 
        :columns="$columns" 
        emptyMessage="Kayıt bulunamadı" 
    />
</x-dashboard-widget>

<!--
KULLANIM ÖRNEKLERİ:

1. Sadece belirli bir müşterinin işleri:
   $items = \App\Models\TumIsler::where('musteri_id', 123)->get();

2. Son 30 gün içinde eklenen işler:
   $items = \App\Models\TumIsler::where('created_at', '>=', now()->subDays(30))->get();

3. Belirli bir tarih aralığı:
   $items = \App\Models\TumIsler::whereBetween('kapanis_tarihi', ['2026-01-01', '2026-12-31'])->get();

4. Çoklu filtre:
   $items = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
       ->whereYear('kapanis_tarihi', 2026)
       ->where('teklif_tutari', '>', 10000)
       ->get();

5. İlişkili model ile filtreleme:
   $items = \App\Models\TumIsler::whereHas('musteri', function($q) {
       $q->where('sehir', 'İstanbul');
   })->get();

6. Ziyaretler için:
   $items = \App\Models\Ziyaret::where('durumu', 'Tamamlandı')
       ->whereMonth('ziyaret_tarihi', now()->month)
       ->get();

7. Müşteriler için:
   $items = \App\Models\Musteri::where('derece', 'A')
       ->withCount('ziyaretler')
       ->orderBy('ziyaretler_count', 'desc')
       ->get();
-->
