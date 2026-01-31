# 🚀 Notion Sync Başlangıç Rehberi

## Adım 1: Notion Integration Oluştur

1. **Notion Integrations sayfasına git:**
   - 🔗 https://www.notion.so/my-integrations
   
2. **"New integration" butonuna tıkla**

3. **Integration ayarlarını yap:**
   - **Name**: `Laravel CRM` (istediğin bir isim)
   - **Workspace**: Çalıştığın workspace'i seç
   - **Capabilities**: 
     - ✅ Read content
     - ✅ Update content
     - ✅ Insert content
   
4. **"Submit" ile oluştur**

5. **Internal Integration Token'ı KOPYALA** 
   - `secret_xxxxxxxxxxxxxxxxxxxx` formatında olacak
   - ⚠️ Bu token'ı kimseyle paylaşma!

---

## Adım 2: Database'i Integration'a Bağla

1. **Notion'da "Tüm İşler" database'ini aç**

2. **Sağ üstte "..." (3 nokta) menüsüne tıkla**

3. **"Add connections" seç**

4. **Oluşturduğun integration'ı seç** (`Laravel CRM`)

5. **"Confirm" ile onayla**

✅ Artık integration database'i okuyabilir!

---

## Adım 3: Database ID'yi Bul

### Yöntem 1: URL'den (Kolay)

1. Database'i full page olarak aç
2. URL'ye bak:
   ```
   https://www.notion.so/workspace/abc123def456789?v=...
                                   ^^^^^^^^^^^^^^^^
                                   Bu kısmı kopyala (32 karakter)
   ```

### Yöntem 2: Share Link'ten

1. Database'de "Share" butonuna bas
2. "Copy link" ile linki kopyala
3. URL'deki son slash `/` ile soru işareti `?` arasındaki kısmı al:
   ```
   https://www.notion.so/abc123def456789?v=xyz
                         ^^^^^^^^^^^^^^^^
   ```

---

## Adım 4: Laravel'e Token Ekle

1. **Terminalini aç ve .env dosyasını düzenle:**

```bash
cd /Users/murat/Herd/my-crm
nano .env
```

2. **En alta şunu ekle** (kendi token'ınla değiştir):

```env
NOTION_API_TOKEN=secret_xxxxxxxxxxxxxxxxxxxx
```

3. **Kaydet ve çık** (Ctrl+O, Enter, Ctrl+X)

---

## Adım 5: İlk Sync'i Çalıştır! 🎉

### Müşterileri Sync Et (Önce bu)

```bash
php artisan notion:sync [MUSTERI_DATABASE_ID] --type=musteriler
```

**Örnek:**
```bash
php artisan notion:sync abc123def456789 --type=musteriler
```

### Markaları Sync Et

```bash
php artisan notion:sync [MARKA_DATABASE_ID] --type=markalar
```

### Tüm İşleri Sync Et

```bash
php artisan notion:sync [TUM_ISLER_DATABASE_ID] --type=tum-isler
```

---

## 📋 Komut Çıktısı Nasıl Olacak

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
  Teklif Tutarı → number
  ...

📥 Veriler senkronize ediliyor...
305/305 [████████████████████] 100%

✅ Senkronizasyon tamamlandı!
┌──────────────┬──────┐
│ Durum        │ Sayı │
├──────────────┼──────┤
│ Yeni Eklenen │ 305  │
│ Güncellenen  │ 0    │
│ Atlanan      │ 0    │
└──────────────┴──────┘
```

---

## ❓ Hata Alırsan

### "Database şeması alınamadı"
- ✅ Token doğru mu kontrol et
- ✅ Database'i integration'a bağladın mı?
- ✅ Database ID doğru mu?

### "Property bulunamadı" 
- Notion'daki property isimleri farklı olabilir
- `app/Console/Commands/SyncNotionData.php` dosyasında `mapNotionToTumIsler()` fonksiyonunu düzenle

### "Rate limit exceeded"
- Notion API saniyede 3 request limiti var
- Komut otomatik bekliyor, sabırlı ol 😊

---

## 🎯 Hızlı Başlangıç (Özet)

```bash
# 1. .env'e token ekle
echo "NOTION_API_TOKEN=secret_xxxx" >> .env

# 2. Müşterileri sync et
php artisan notion:sync ABC123 --type=musteriler

# 3. Markaları sync et  
php artisan notion:sync DEF456 --type=markalar

# 4. İşleri sync et
php artisan notion:sync GHI789 --type=tum-isler

# 5. Web'i aç ve gör!
# http://my-crm.test/tum-isler
```

---

## 📸 Notion Property İsimleri

Komut şu property isimlerini bekliyor:

### Tüm İşler Database'i:
- **Name** veya **İş Adı** (Title)
- **Müşteri** (Relation to Müşteriler)
- **Marka** (Relation to Markalar)
- **Tipi** (Select)
- **Durum** (Select)
- **Türü** (Select)
- **Öncelik** (Select: 1,2,3,4)
- **Register Durumu** (Select)
- **Teklif Tutarı** (Number)
- **Alış Tutarı** (Number)
- **Kur** (Number)
- **Teklif Döviz** (Select: TL, USD, EUR)
- **Alış Döviz** (Select: TL, USD, EUR)
- **Açılış Tarihi** (Date)
- **Kapanış Tarihi** (Date)
- **Lisans Bitiş** (Date)
- **Notlar** (Text)
- **Açıklama** (Text)

⚠️ Property isimlerin farklıysa mapping'i düzenlemen gerekir!

---

## 🔄 Güncellemeleri Sync Et

Notion'da değişiklik yaptıktan sonra:

```bash
php artisan notion:sync [DATABASE_ID] --type=tum-isler
```

Laravel'de değişiklik yaptıktan sonra:

```bash
php artisan notion:push [DATABASE_ID] --type=tum-isler
```

---

## 🎉 İlk Sync Başarılı mı?

Web'i aç ve kontrol et:
- http://my-crm.test/tum-isler
- Sağ üstte "🔗 X kayıt Notion'dan senkronize" yazısını göreceksin
- Her satırda Notion badge'i olacak

**BAŞARILI! 🚀**
