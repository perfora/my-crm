<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tum_isler', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // İş adı
            
            // İlişkiler (şimdilik foreignId olarak bırakıyoruz, sonra ilişkileri kuracağız)
            $table->foreignId('musteri_id')->nullable()->constrained('musteriler')->onDelete('set null');
            $table->foreignId('marka_id')->nullable()->constrained('markalar')->onDelete('set null');
            
            // Seçim alanları (select)
            $table->enum('tipi', [
                'Takip Edilecek', 'Tamamlandı', 'Vazgeçildi', 'Kaybedildi', 
                'Kazanıldı', 'Verilecek', 'Siparişler', 'Register', 
                'Diğer', 'Ödeme', 'Verildi', 'Askıda', 'Seçilmedi (Alternatif)'
            ])->nullable();
            
            $table->enum('turu', [
                'Hizmet Alımı', 'Yazılım ve Lisans', 'Cihaz ve Lisans', 
                'Yenileme', 'Cihaz', 'Destek'
            ])->nullable();
            
            $table->enum('oncelik', ['1', '2', '3', '4'])->nullable();
            
            $table->enum('kaybedilme_nedeni', [
                'Diğer', 'Bütçe Yok', 'Kendileri Kurdu', 'Müşteri Vazgeçti',
                'Yerli Ürün Tercihi', 'Vade/Ödeme Koşulu', 'Stok Yok',
                'Rakip Daha Ucuz', 'Fiyat Yüksek'
            ])->nullable();
            
            $table->enum('register_durum', [
                'Açık', 'Uzatım İstendi', 'Uzatıldı', 'Kapatıldı'
            ])->nullable();
            
            // Sayısal alanlar
            $table->decimal('teklif_tutari', 15, 2)->nullable();
            $table->decimal('alis_tutari', 15, 2)->nullable();
            $table->decimal('kur', 10, 4)->nullable();
            
            // Tarih alanları
            $table->date('kapanis_tarihi')->nullable();
            $table->date('lisans_bitis')->nullable();
            $table->date('is_guncellenme_tarihi')->nullable();
            
            // Metin alanları
            $table->text('notlar')->nullable();
            $table->text('gecmis_notlar')->nullable();
            $table->text('aciklama')->nullable();
            
            $table->timestamps(); // created_at ve updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tum_isler');
    }
};