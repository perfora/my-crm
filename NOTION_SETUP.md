# 🔗 Notion API Entegrasyonu

## 📋 Kurulum Adımları

### 1. Notion Integration Oluştur

1. [Notion Integrations](https://www.notion.so/my-integrations) sayfasına git
2. "New integration" butonuna tıkla
3. İsim ver: "Laravel CRM Integration"
4. Capabilities'i seç:
   - ✅ Read content
   - ✅ Update content (opsiyonel)
   - ✅ Insert content (opsiyonel)
5. "Submit" ile oluştur
6. **Internal Integration Token**'ı kopyala

### 2. Database'i Integration'a Bağla

1. Notion'da senkronize etmek istediğin database'i aç
2. Sağ üst köşede "..." menüsüne tıkla
3. **"Add connections"** seç
4. Oluşturduğun integration'ı seç: "Laravel CRM Integration"

### 3. Database ID'yi Bul

Database ID'yi bulmak için 2 yöntem:

**Yöntem 1: URL'den**
```
https://www.notion.so/workspace/DATABASE_ID?v=...
                              ^^^^^^^^^^^^^^^^
                              Bu kısmı kopyala
```

**Yöntem 2: Share Link**
- Database'de "Share" butonuna bas
- "Copy link" ile linki al
- Son "/" ile "?" arasındaki ID'yi kopyala

### 4. Laravel .env Ayarı

`.env` dosyana ekle:

```env
NOTION_API_TOKEN=secret_xxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

## 🚀 Kullanım

### Notion'dan Tüm İşleri Çek

```bash
php artisan notion:sync DATABASE_ID --type=tum-isler
```

### Notion'dan Müşterileri Çek

```bash
php artisan notion:sync DATABASE_ID --type=musteriler
```

## 📊 Property Mapping

Komut çalıştığında Notion property'lerini otomatik map eder:

### Tüm İşler Database'i İçin Beklenen Property İsimleri:

- **Name** veya **İş Adı** → İş adı
- **Müşteri** → Müşteri (otomatik oluşturur/bulur)
- **Marka** → Marka (otomatik oluşturur/bulur)
- **Tipi** → Tipi (Verildi, Kazanıldı, vs.)
- **Durum** → Durum (Aktif/Pasif)
- **Türü** → İş türü
- **Öncelik** → Öncelik (1-4)
- **Register Durumu** → Register durumu
- **Teklif Tutarı** → Teklif tutarı (Number)
- **Alış Tutarı** → Alış tutarı (Number)
- **Maliyet Tutarı** → Maliyet tutarı (Number)
- **Kur** → Kur (Number)
- **Teklif Döviz** → Teklif döviz türü
- **Alış Döviz** → Alış döviz türü
- **Açılış Tarihi** → İş açılış tarihi (Date)
- **Kapanış Tarihi** → Kapanış tarihi (Date)
- **Lisans Bitiş** → Lisans bitiş tarihi (Date)
- **Açıklama** → Açıklama metni
- **Notlar** → Notlar
- **Geçmiş Notlar** → Geçmiş notlar
- **Kaybedilme Nedeni** → Kaybedilme nedeni

### Property İsimlerini Özelleştirmek

Property isimlerin farklıysa, `/app/Console/Commands/SyncNotionData.php` dosyasında `mapNotionToTumIsler()` fonksiyonunu düzenle.

Örnek:
```php
// Notion'da "Title" yerine "Project Name" kullanıyorsan:
if (isset($record['Project Name'])) {
    $data['name'] = $record['Project Name'];
}
```

## 🔄 Senkronizasyon Davranışı

- **Notion ID ile takip eder**: Aynı kayıt tekrar import edilmez, güncellenir
- **İlişkileri otomatik oluşturur**: Müşteri/Marka yoksa oluşturur
- **Güvenli**: Mevcut verileri silmez, sadece günceller veya yeni ekler

## 📈 Örnek Çıktı

```bash
$ php artisan notion:sync abc123def456 --type=tum-isler

🔄 Notion'dan veri çekiliyor...
✓ Database: Tüm İşler CRM
✓ 305 kayıt bulundu

📋 Notion Property Mapping:
  Name → title
  Müşteri → relation
  Marka → relation
  Tipi → select
  ...

📥 Veriler senkronize ediliyor...
305/305 [████████████████████] 100%

✅ Senkronizasyon tamamlandı!
┌──────────────┬──────┐
│ Durum        │ Sayı │
├──────────────┼──────┤
│ Yeni Eklenen │ 12   │
│ Güncellenen  │ 293  │
│ Atlanan      │ 0    │
└──────────────┴──────┘
```

## ⚠️ Önemli Notlar

1. **İlk Senkronizasyon**: Tüm kayıtları çeker ve `notion_id` ile işaretler
2. **Sonraki Senkronizasyonlar**: Sadece değişen kayıtları günceller
3. **İki Yönlü Değil**: Laravel'den Notion'a otomatik senkron yok (eklenebilir)
4. **Rate Limit**: Notion API saniyede 3 request limiti var, büyük database'lerde biraz yavaş olabilir

## 🛠️ Troubleshooting

### "Database şeması alınamadı" Hatası
- API token doğru mu kontrol et
- Database'i integration'a bağladın mı kontrol et
- Database ID doğru mu kontrol et

### Property Mapping Hataları
- Notion property isimlerini komut çıktısından kontrol et
- `mapNotionToTumIsler()` fonksiyonunda mapping'i düzenle

### İlişki Hataları (Müşteri/Marka Bulunamadı)
- Notion'da Relation olarak tanımlı mı kontrol et
- Önce müşterileri, sonra işleri import et

## 🎯 Avantajlar vs CSV Import

| Özellik | CSV Import | Notion API |
|---------|------------|------------|
| Hız | ⚡ Hızlı (tek seferlik) | 🐌 Yavaş (API limit) |
| Doğruluk | ❌ Manuel mapping, hataya açık | ✅ Otomatik, tutarlı |
| Güncelleme | ❌ Her seferinde export/import | ✅ Tek komutla güncelle |
| İlişkiler | ⚠️ Manuel eşleştirme | ✅ Otomatik çözümler |
| Geçmiş | ❌ Notion'daki değişiklikler kaybolur | ✅ Notion'da güncellemeleri takip eder |

## 🚀 İleriye Dönük Özellikler

- [ ] Zamanlanmış otomatik senkronizasyon (Cron)
- [ ] Laravel'den Notion'a veri gönderme
- [ ] İki yönlü senkronizasyon
- [ ] Webhook ile anlık senkronizasyon
- [ ] Seçici senkronizasyon (sadece yeni kayıtlar)
