# 📊 CRM Dashboard Widget Sistemi

## Hızlı Başlangıç

### Widget'ları Açıp Kapatma

`resources/views/dashboard.blade.php` dosyasını aç ve şu bölümü bul:

```php
$widgets = [
    'ozet_kartlar' => true,        // Toplam Müşteri, Kişi, Ziyaret, İş kartları
    'yillik_karsilastirma' => true, // 2025 vs 2026 karşılaştırma
    'bekleyen_isler' => true,      // Verilecek/Takip edilecek işler
    'bu_ay_kazanilan' => true,     // Bu ay kazanılan işler
    'yuksek_oncelikli' => true,    // Öncelik=1 olan işler
    'yaklasan_ziyaretler' => true, // Planlanmış ziyaretler
];
```

İstediğini `false` yap, gizlenir:
```php
'yuksek_oncelikli' => false,  // Artık gözükmeyecek
```

---

## Yeni Widget Oluşturma

### Adım 1: Widget Dosyası Oluştur

```bash
# Şablon dosyasını kopyala
cp resources/views/widgets/_SABLOM.blade.php resources/views/widgets/benim-widget.blade.php
```

### Adım 2: Widget'ı Düzenle

`resources/views/widgets/benim-widget.blade.php` dosyasını aç:

```php
@php
    // Sorguyu değiştir
    $items = \App\Models\TumIsler::where('tipi', 'Askıda')
        ->with('musteri')
        ->get();
    
    // Sütunları tanımla
    $columns = [
        ['label' => 'İş Adı', 'field' => 'name'],
        ['label' => 'Müşteri', 'format' => function($item) {
            return $item->musteri ? $item->musteri->sirket : '-';
        }],
    ];
@endphp

<x-dashboard-widget title="Askıdaki İşler" noPadding="true">
    <x-data-table :items="$items" :columns="$columns" />
</x-dashboard-widget>
```

### Adım 3: Dashboard'a Ekle

`resources/views/dashboard.blade.php` içinde:

1. Widget ayarlarına ekle:
```php
$widgets = [
    'ozet_kartlar' => true,
    'benim_widget' => true,  // YENİ
];
```

2. Widget alanına ekle:
```php
<div class="space-y-6">
    
    @if($widgets['benim_widget'])
        @include('widgets.benim-widget')
    @endif
    
</div>
```

---

## Örnek Widget'lar

### 1. Son 7 Gün İçinde Eklenen İşler

```php
@php
    $items = \App\Models\TumIsler::where('created_at', '>=', now()->subDays(7))
        ->with('musteri')
        ->orderBy('created_at', 'desc')
        ->get();
    
    $columns = [
        ['label' => 'İş', 'field' => 'name'],
        ['label' => 'Müşteri', 'format' => fn($item) => 
            $item->musteri ? $item->musteri->sirket : '-'],
        ['label' => 'Tarih', 'format' => fn($item) => 
            $item->created_at->format('d.m.Y')],
    ];
@endphp
```

### 2. A Dereceli Müşteriler

```php
@php
    $items = \App\Models\Musteri::where('derece', 'A')
        ->withCount('tumIsler')
        ->orderBy('tum_isler_count', 'desc')
        ->get();
    
    $columns = [
        ['label' => 'Firma', 'field' => 'sirket'],
        ['label' => 'Şehir', 'field' => 'sehir'],
        ['label' => 'İş Sayısı', 'field' => 'tum_isler_count'],
    ];
@endphp
```

### 3. Lisansı Biten İşler (30 gün içinde)

```php
@php
    $items = \App\Models\TumIsler::whereBetween('lisans_bitis', [
            now(),
            now()->addDays(30)
        ])
        ->with('musteri')
        ->orderBy('lisans_bitis', 'asc')
        ->get();
    
    $columns = [
        ['label' => 'İş', 'field' => 'name'],
        ['label' => 'Müşteri', 'format' => fn($item) => 
            $item->musteri ? $item->musteri->sirket : '-'],
        ['label' => 'Bitiş', 'format' => fn($item) => 
            \Carbon\Carbon::parse($item->lisans_bitis)->format('d.m.Y')],
    ];
@endphp
```

---

## Sütun Formatları

### Basit Alan
```php
['label' => 'İş Adı', 'field' => 'name']
```

### Özel Format
```php
['label' => 'Müşteri', 'format' => function($item) {
    return '<span class="text-blue-600">' . $item->musteri->sirket . '</span>';
}]
```

### Badge/Etiket
```php
['label' => 'Durum', 'format' => function($item) {
    $renk = $item->tipi == 'Kazanıldı' ? 'green' : 'yellow';
    return '<span class="px-2 py-1 text-xs rounded-full bg-' . $renk . '-100 text-' . $renk . '-800">' 
           . $item->tipi . '</span>';
}]
```

### Para Formatı
```php
['label' => 'Tutar', 'format' => function($item) {
    return $item->teklif_tutari ? '$' . number_format($item->teklif_tutari, 2) : '-';
}]
```

### Tarih Formatı
```php
['label' => 'Tarih', 'format' => function($item) {
    return $item->created_at->format('d.m.Y H:i');
}]
```

---

## Layout Seçenekleri

### Tam Genişlik
```php
@include('widgets.benim-widget')
```

### Yan Yana (2 kolon)
```php
<div class="grid grid-cols-2 gap-6">
    <div>@include('widgets.widget-1')</div>
    <div>@include('widgets.widget-2')</div>
</div>
```

### 3 Kolon
```php
<div class="grid grid-cols-3 gap-6">
    <div>@include('widgets.widget-1')</div>
    <div>@include('widgets.widget-2')</div>
    <div>@include('widgets.widget-3')</div>
</div>
```

---

## Hazır Widget'lar

- `widgets/bekleyen-isler.blade.php` - Verilecek/Takip işler
- `widgets/bu-ay-kazanilan.blade.php` - Bu ay kazanılan işler
- `widgets/yuksek-oncelikli.blade.php` - Öncelik 1 işler
- `widgets/yaklasan-ziyaretler.blade.php` - Planlanmış ziyaretler
- `widgets/_SABLOM.blade.php` - Yeni widget şablonu

---

## Dosya Konumları

```
resources/views/
├── dashboard.blade.php          # Ana dashboard (buradan widget'ları yönet)
├── widgets/
│   ├── _SABLOM.blade.php       # Yeni widget şablonu
│   ├── bekleyen-isler.blade.php
│   ├── bu-ay-kazanilan.blade.php
│   ├── yuksek-oncelikli.blade.php
│   └── yaklasan-ziyaretler.blade.php
└── components/
    ├── dashboard-widget.blade.php  # Widget container
    └── data-table.blade.php        # Tablo component
```

---

## İpuçları

1. **Widget'ı test et**: Yeni widget oluşturduktan sonra dashboard'u yenile
2. **Cache temizle**: `php artisan view:clear` komutu ile cache'i temizle
3. **Kolay debug**: Widget'ta hata varsa, widget'ı `false` yap, dashboard yüklensin
4. **Sorgu optimizasyonu**: `->limit(10)` ile sadece ilk 10 kayıt al
5. **İlişkileri yükle**: `->with('musteri')` ile ilişkili verileri önceden yükle

---

## Yardım

Widget oluştururken takıldıysan:
1. `widgets/_SABLOM.blade.php` dosyasındaki örneklere bak
2. Mevcut widget'ları (`bekleyen-isler`, `bu-ay-kazanilan`) incele
3. Laravel query builder dokümantasyonuna bak: https://laravel.com/docs/queries
