# CRM Yapı Özeti (AI Paylaşım İndeksi)

Bu doküman, CRM yapısını bir AI araca kısa ve net şekilde anlatmak için hazırlandı.
Odak: CRM operasyon sayfaları, sütunlar, temel veri modeli, dashboard widget mantığı.
Not: Finans tarafı bu özetten bilinçli olarak hariç tutuldu.

## 1) Ana Modüller ve Sayfalar

- `Ana Sayfa / Dashboard` (`/dashboard`)
- `Tüm İşler` (`/tum-isler`)
- `Firmalar` (`/musteriler`)
- `Markalar` (`/markalar`)
- `Kişiler` (`/kisiler`)
- `Teklifler` (`/fiyat-teklifleri`)
- `Teklif Koşulları` (`/teklif-kosullari`)
- `Ürünler` (`/urunler`)
- `Tedarikçi Fiyatları` (`/tedarikci-fiyatlari`)
- `Takvim` (`/takvim`)
- `Ziyaretler` (`/ziyaretler`)
- `Raporlar` (`/raporlar`)
- `Sistem > Loglar` (`/sistem-loglari`)
- `Sistem > AI API` (`/sistem/ai-api`)
- `Sistem > Dışa Aktar` (`/sistem/disa-aktar`)

## 2) Tablo Sütunları (Operasyonel Sayfalar)

### 2.1 Tüm İşler (`/tum-isler`)
- İş Adı
- Müşteri
- Marka
- Tipi
- Kaybedilme Nedeni
- Durum
- Türü
- Öncelik
- Kapanış
- Lisans Bitiş
- Teklif
- Alış
- Kur
- Kar
- Açılış
- Güncelleme
- Notlar
- Geçmiş Notlar
- Yenileme (aksiyon kolonu)

### 2.2 Firmalar (`/musteriler`)
- Şirket
- Şehir
- Telefon
- Derece
- Türü
- Arama Periyodu (gün)
- Ziyaret Periyodu (gün)
- Temas Kuralı
- Adres
- Notlar
- Hızlı (hızlı arama/ziyaret aksiyonları)
- Son Bağlantı
- Bağlantı Türü
- Bağlantı Gün
- Ziyaret Adeti
- Toplam Teklif
- Kazanıldı Toplamı

### 2.3 Ziyaretler (`/ziyaretler`)
- Ziyaret
- Müşteri
- Ziyaret Tarihi
- Tür
- Durum
- Notlar

### 2.4 Markalar (`/markalar`)
- Marka Adı
- Tarih

### 2.5 Kişiler (`/kisiler`)
- Ad Soyad
- Firma
- Telefon
- Email
- Bölüm
- Görev
- URL

### 2.6 Teklifler (`/fiyat-teklifleri`)
- Teklif No
- Müşteri
- Tarih
- Toplam Satış
- Kar
- Durum
- İşlemler

### 2.7 Ürünler (`/urunler`)
- Ürün Adı
- Marka
- Kategori
- Stok Kodu
- Son Alış
- Kar Oranı
- İşlemler

### 2.8 Tedarikçi Fiyatları (`/tedarikci-fiyatlari`)
- Tedarikçi
- Ürün
- Tarih
- Birim Fiyat
- Para Birimi
- Min. Sipariş
- İşlemler

## 3) Dashboard Widget Yapısı (Özet)

Dashboard’da operasyonu yöneten widget seti var. Mevcut tablolu widget’larda ana odak:

- Bekleyen İşler (İş + Müşteri)
- Register/Kapanış odaklı işler (İş + Kapanış)
- Takip Edilecek İşler (İş + Açılış)
- Temas takibi odaklı listeler:
  - Ziyaret Gerekli
  - Arama Gerekli
  - Her İkisi Gerekli
  - Kolonlar: Müşteri, son ziyaret bilgisi, son arama bilgisi
  - Periyot farkı (gecikme) mantığı ile sıralama

## 4) AI İçin Kritik Alan Mantığı

- `Tipi` işin yaşam evresidir (ör. Verilecek, Verildi, Kazanıldı, Kaybedildi, Takip Edilecek, Register vb.).
- `Durum` operasyonel durumdur (örn. aktif/pasif veya süreç durumu).
- `Türü` iş kategorisidir (Cihaz, Yazılım/Lisans, Destek, Yenileme vb.).
- `Öncelik` işin önem sıralamasıdır.
- `Açılış / Kapanış / Güncelleme` tarihleri farklı amaç taşır.
- `Notlar` ve `Geçmiş Notlar` süreç bilgisini tutar.
- `Firmalar` sayfasındaki temas periyotları (arama/ziyaret) dashboard temas widget’larını besler.

## 5) Kullanılabilir API Uçları (Özet)

Read-only AI ve dashboard/rapor uçlarından bazıları:

- `GET /api/ai/summary/dashboard`
- `GET /api/ai/tum-isler`
- `GET /api/ai/musteriler`
- `GET /api/ai/ziyaretler`
- `POST /api/filter-widget-data`
- `POST /api/rapor-marka`
- `POST /api/rapor-musteri`
- `POST /api/yenileme-ac`
- `POST /api/yenileme-isaretle`

## 6) AI’ye Verilecek Kısa Prompt Şablonu

Bu CRM’de:
- satış pipeline’ı `Tüm İşler` tablosunda yönetiliyor,
- müşteri ilişki sıklığı `Firmalar` + `Ziyaretler` üzerinden izleniyor,
- dashboard karar listeleri periyot gecikmesine göre üretiliyor.

Görev:
1. Eksik olabilecek veri alanlarını öner.
2. Yönetici için en kritik 5 widget öner (widget adı + kolonlar + sıralama mantığı).
3. Satış takibi için haftalık aksiyon listesi üretme metodunu tasarla.
4. Mevcut kolonları bozmadan minimum ekleme ile iyileştirme öner.

Kısıt:
- Finans modülüne girme.
- Mevcut operasyon akışını bozacak öneri verme.
