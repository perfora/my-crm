# 🔄 İki Yönlü Notion Senkronizasyonu

## 📥 Notion → Laravel

### İlk Senkronizasyon
```bash
php artisan notion:sync DATABASE_ID --type=tum-isler
```

### Notion'dan Güncellemeleri Çek
```bash
php artisan notion:sync DATABASE_ID --type=tum-isler
```

Notion'da değişiklik yaptıktan sonra bu komutu çalıştır, Laravel'e yansır.

---

## 📤 Laravel → Notion

### Yeni Kayıtları Notion'a Gönder
```bash
php artisan notion:push DATABASE_ID --type=tum-isler
```

Bu komut:
- ✅ Notion ID'si olmayan kayıtları **yeni oluşturur**
- ✅ Son 24 saatte güncellenen kayıtları **günceller**
- ✅ Notion ID'yi otomatik kaydeder

### Tüm Kayıtları Zorla Gönder
```bash
php artisan notion:push DATABASE_ID --type=tum-isler --force
```

`--force` ile tüm kayıtlar Notion'a gönderilir (yavaş olabilir).

---

## 🔄 İki Yönlü Workflow

### Senaryo 1: Notion'da Değişiklik
```bash
# Notion'da iş ekledin/düzenledin
php artisan notion:sync TUM_ISLER_DATABASE_ID --type=tum-isler

# Laravel'de güncel veriler görünür!
```

### Senaryo 2: Laravel'de Değişiklik
```bash
# Laravel'de (web arayüzünde) iş ekledin/düzenledin
php artisan notion:push TUM_ISLER_DATABASE_ID --type=tum-isler

# Notion'da güncel veriler görünür!
```

### Senaryo 3: Her İkisinde de Değişiklik
```bash
# Önce Notion'dan çek
php artisan notion:sync TUM_ISLER_DATABASE_ID --type=tum-isler

# Sonra Laravel'den gönder
php artisan notion:push TUM_ISLER_DATABASE_ID --type=tum-isler
```

---

## ⚙️ Otomatik Senkronizasyon (Opsiyonel)

Laravel'den Notion'a otomatik göndermek için Model Event ekle:

### routes/console.php'ye ekle:
```php
use Illuminate\Support\Facades\Schedule;

// Her 30 dakikada bir Laravel'deki değişiklikleri Notion'a gönder
Schedule::command('notion:push ' . env('NOTION_TUM_ISLER_DB_ID') . ' --type=tum-isler')
    ->everyThirtyMinutes();

// Her saat Notion'dan güncellemeleri çek
Schedule::command('notion:sync ' . env('NOTION_TUM_ISLER_DB_ID') . ' --type=tum-isler')
    ->hourly();
```

### .env'ye ekle:
```env
NOTION_TUM_ISLER_DB_ID=abc123def456
NOTION_MUSTERILER_DB_ID=xyz789ghi012
NOTION_MARKALAR_DB_ID=lmn345opq678
```

### Cron'u Başlat:
```bash
# Laravel Scheduler'ı çalıştır
php artisan schedule:work
```

---

## 📊 Desteklenen Tipleri

### Tüm İşler
```bash
php artisan notion:sync DATABASE_ID --type=tum-isler
php artisan notion:push DATABASE_ID --type=tum-isler
```

### Müşteriler
```bash
php artisan notion:sync DATABASE_ID --type=musteriler
php artisan notion:push DATABASE_ID --type=musteriler
```

### Markalar
```bash
php artisan notion:sync DATABASE_ID --type=markalar
php artisan notion:push DATABASE_ID --type=markalar
```

---

## 🎯 Senkronizasyon Mantığı

### notion:sync (Notion → Laravel)
```
Notion'daki kayıt var mı?
├─ Evet → notion_id ile bul
│  ├─ Laravel'de var → GÜNCELLE
│  └─ Laravel'de yok → YENİ OLUŞTUR (notion_id kaydet)
└─ Hayır → Atla
```

### notion:push (Laravel → Notion)
```
Laravel'deki kayıt:
├─ notion_id var mı?
│  ├─ Evet → Notion'da GÜNCELLE
│  └─ Hayır → Notion'da YENİ OLUŞTUR (notion_id'yi Laravel'e kaydet)
└─ Son 24 saatte güncellendi mi?
   ├─ Evet → Notion'a gönder
   └─ Hayır (ve --force yok) → Atla
```

---

## ⚠️ Önemli Notlar

### 1. Rate Limit
- Notion API: **Saniyede 3 request**
- Komutlar otomatik 350ms bekliyor
- Büyük senkronizasyonlar yavaş olabilir

### 2. İlişkiler (Relations)
- Müşteri/Marka push edilirken önce onların notion_id'si olmalı
- Sıralama: **Önce Müşteriler → Sonra Markalar → En son Tüm İşler**

```bash
# Doğru sıra:
php artisan notion:push MUSTERI_DB_ID --type=musteriler
php artisan notion:push MARKA_DB_ID --type=markalar
php artisan notion:push TUM_ISLER_DB_ID --type=tum-isler
```

### 3. Çakışma Riski
- Aynı anda hem Notion'da hem Laravel'de aynı kaydı değiştirme
- **Son yazılan kazanır** mantığı çalışır
- Kritik kayıtlar için tek taraftan düzenle

### 4. Text Limitleri
- Notion rich_text alanları **2000 karakter** ile sınırlı
- Uzun notlar otomatik kısaltılır

---

## 🧪 Test Senaryosu

### 1. İlk Kurulum
```bash
# Notion'dan tüm verileri çek
php artisan notion:sync TUM_ISLER_DB_ID --type=tum-isler

# Notion ID'si olmayanları geri gönder (varsa)
php artisan notion:push TUM_ISLER_DB_ID --type=tum-isler
```

### 2. Günlük Kullanım
```bash
# Sabah: Notion'dan güncellemeleri çek
php artisan notion:sync TUM_ISLER_DB_ID --type=tum-isler

# Akşam: Laravel'deki değişiklikleri gönder
php artisan notion:push TUM_ISLER_DB_ID --type=tum-isler
```

### 3. Laravel'e Tam Geçiş
```bash
# Notion'dan son kez tüm verileri çek
php artisan notion:sync TUM_ISLER_DB_ID --type=tum-isler

# Artık sadece Laravel'i kullan
# notion:push komutuna gerek kalmaz
```

---

## 📈 İleriye Dönük

### Webhook (Anlık Senkronizasyon)
- Notion webhook desteği sınırlı
- Laravel'de değişiklik → Anında Notion'a gönder (Event listener ile)
- Şimdilik manuel/zamanlanmış sync öneriliyor

### Conflict Resolution
- Çakışma tespiti
- Manuel merge ekranı
- "Last write wins" yerine "Smart merge"

### Selective Sync
- Sadece belirli kayıtları sync et
- Filtre bazlı senkronizasyon
- Tag/kategori bazlı ayırma

---

## ✅ Özet

| Komut | Ne Yapar | Ne Zaman Kullan |
|-------|----------|-----------------|
| `notion:sync` | Notion → Laravel | Notion'da değişiklik sonrası |
| `notion:push` | Laravel → Notion | Laravel'de değişiklik sonrası |
| `--force` | Tüm kayıtları zorla | İlk kurulum, büyük değişiklikler |
| Schedule | Otomatik sync | Laravel'e oturunca |

Laravel'e tam geçene kadar her iki komutu da düzenli kullan! 🚀
