<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İş Düzenle - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-4">
            <a href="/tum-isler" class="text-blue-600 hover:underline">← Geri</a>
        </div>
        
        <h1 class="text-3xl font-bold mb-6">İş Düzenle</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="/tum-isler/{{ $is->id }}" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">İş Adı *</label>
                        <input type="text" name="name" value="{{ $is->name }}" required class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Müşteri</label>
                        <select name="musteri_id" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            @php
                                $musteriler = \App\Models\Musteri::orderBy('sirket')->get();
                            @endphp
                            @foreach($musteriler as $musteri)
                                <option value="{{ $musteri->id }}" {{ $is->musteri_id == $musteri->id ? 'selected' : '' }}>
                                    {{ $musteri->sirket }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Marka</label>
                        <select name="marka_id" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            @php
                                $markalar = \App\Models\Marka::orderBy('name')->get();
                            @endphp
                            @foreach($markalar as $marka)
                                <option value="{{ $marka->id }}" {{ $is->marka_id == $marka->id ? 'selected' : '' }}>
                                    {{ $marka->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipi</label>
                        <select name="tipi" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="Verilecek" {{ $is->tipi == 'Verilecek' ? 'selected' : '' }}>Verilecek</option>
                            <option value="Verildi" {{ $is->tipi == 'Verildi' ? 'selected' : '' }}>Verildi</option>
                            <option value="Takip Edilecek" {{ $is->tipi == 'Takip Edilecek' ? 'selected' : '' }}>Takip Edilecek</option>
                            <option value="Kazanıldı" {{ $is->tipi == 'Kazanıldı' ? 'selected' : '' }}>Kazanıldı</option>
                            <option value="Kaybedildi" {{ $is->tipi == 'Kaybedildi' ? 'selected' : '' }}>Kaybedildi</option>
                            <option value="Askıda" {{ $is->tipi == 'Askıda' ? 'selected' : '' }}>Askıda</option>
                            <option value="Register" {{ $is->tipi == 'Register' ? 'selected' : '' }}>Register</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Türü</label>
                        <select name="turu" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="Cihaz" {{ $is->turu == 'Cihaz' ? 'selected' : '' }}>Cihaz</option>
                            <option value="Yazılım ve Lisans" {{ $is->turu == 'Yazılım ve Lisans' ? 'selected' : '' }}>Yazılım ve Lisans</option>
                            <option value="Cihaz ve Lisans" {{ $is->turu == 'Cihaz ve Lisans' ? 'selected' : '' }}>Cihaz ve Lisans</option>
                            <option value="Yenileme" {{ $is->turu == 'Yenileme' ? 'selected' : '' }}>Yenileme</option>
                            <option value="Destek" {{ $is->turu == 'Destek' ? 'selected' : '' }}>Destek</option>
                            <option value="Hizmet Alımı" {{ $is->turu == 'Hizmet Alımı' ? 'selected' : '' }}>Hizmet Alımı</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Öncelik</label>
                        <select name="oncelik" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="1" {{ $is->oncelik == '1' ? 'selected' : '' }}>1 (Yüksek)</option>
                            <option value="2" {{ $is->oncelik == '2' ? 'selected' : '' }}>2</option>
                            <option value="3" {{ $is->oncelik == '3' ? 'selected' : '' }}>3</option>
                            <option value="4" {{ $is->oncelik == '4' ? 'selected' : '' }}>4 (Düşük)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Teklif Tutarı (TL)</label>
                        <input type="number" step="0.01" name="teklif_tutari" value="{{ $is->teklif_tutari }}" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Alış Tutarı (TL)</label>
                        <input type="number" step="0.01" name="alis_tutari" value="{{ $is->alis_tutari }}" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Kur</label>
                        <input type="number" step="0.0001" name="kur" value="{{ $is->kur }}" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Kapanış Tarihi</label>
                        <input type="date" name="kapanis_tarihi" 
                               value="{{ $is->kapanis_tarihi ? $is->kapanis_tarihi->format('Y-m-d') : '' }}" 
                               class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Lisans Bitiş</label>
                        <input type="date" name="lisans_bitis" 
                               value="{{ $is->lisans_bitis ? $is->lisans_bitis->format('Y-m-d') : '' }}" 
                               class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Açıklama</label>
                    <textarea name="aciklama" rows="2" class="w-full border rounded px-3 py-2">{{ $is->aciklama }}</textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Kaybedilme Nedeni</label>
                        <select name="kaybedilme_nedeni" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="Diğer" {{ $is->kaybedilme_nedeni == 'Diğer' ? 'selected' : '' }}>Diğer</option>
                            <option value="Bütçe Yok" {{ $is->kaybedilme_nedeni == 'Bütçe Yok' ? 'selected' : '' }}>Bütçe Yok</option>
                            <option value="Kendileri Kurdu" {{ $is->kaybedilme_nedeni == 'Kendileri Kurdu' ? 'selected' : '' }}>Kendileri Kurdu</option>
                            <option value="Müşteri Vazgeçti" {{ $is->kaybedilme_nedeni == 'Müşteri Vazgeçti' ? 'selected' : '' }}>Müşteri Vazgeçti</option>
                            <option value="Yerli Ürün Tercihi" {{ $is->kaybedilme_nedeni == 'Yerli Ürün Tercihi' ? 'selected' : '' }}>Yerli Ürün Tercihi</option>
                            <option value="Vade/Ödeme Koşulu" {{ $is->kaybedilme_nedeni == 'Vade/Ödeme Koşulu' ? 'selected' : '' }}>Vade/Ödeme Koşulu</option>
                            <option value="Stok Yok" {{ $is->kaybedilme_nedeni == 'Stok Yok' ? 'selected' : '' }}>Stok Yok</option>
                            <option value="Rakip Daha Ucuz" {{ $is->kaybedilme_nedeni == 'Rakip Daha Ucuz' ? 'selected' : '' }}>Rakip Daha Ucuz</option>
                            <option value="Fiyat Yüksek" {{ $is->kaybedilme_nedeni == 'Fiyat Yüksek' ? 'selected' : '' }}>Fiyat Yüksek</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Register Durum</label>
                        <select name="register_durum" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="Açık" {{ $is->register_durum == 'Açık' ? 'selected' : '' }}>Açık</option>
                            <option value="Uzatım İstendi" {{ $is->register_durum == 'Uzatım İstendi' ? 'selected' : '' }}>Uzatım İstendi</option>
                            <option value="Uzatıldı" {{ $is->register_durum == 'Uzatıldı' ? 'selected' : '' }}>Uzatıldı</option>
                            <option value="Kapatıldı" {{ $is->register_durum == 'Kapatıldı' ? 'selected' : '' }}>Kapatıldı</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-medium">Notlar</label>
                        <button type="button" onclick="addToHistory()" class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                            ⬇️ Geçmişe Ekle
                        </button>
                    </div>
                    <textarea id="notlar" name="notlar" rows="3" class="w-full border rounded px-3 py-2">{{ $is->notlar }}</textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Geçmiş Notlar</label>
                    <textarea id="gecmis_notlar" name="gecmis_notlar" rows="5" class="w-full border rounded px-3 py-2 bg-gray-50">{{ $is->gecmis_notlar }}</textarea>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                        Güncelle
                    </button>
                    <a href="/tum-isler" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                        İptal
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function addToHistory() {
            const notlar = document.getElementById('notlar');
            const gecmisNotlar = document.getElementById('gecmis_notlar');
            
            if (!notlar.value.trim()) {
                alert('Not alanı boş!');
                return;
            }
            
            // Tarih ve saat
            const now = new Date();
            const tarih = now.toLocaleDateString('tr-TR', { 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric' 
            });
            const saat = now.toLocaleTimeString('tr-TR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            // Yeni not: [30.01.2026 14:35] Not içeriği
            const yeniNot = `[${tarih} ${saat}] ${notlar.value.trim()}`;
            
            // Geçmiş notlara ekle (en yeni üstte)
            if (gecmisNotlar.value.trim()) {
                gecmisNotlar.value = yeniNot + '\n\n' + gecmisNotlar.value;
            } else {
                gecmisNotlar.value = yeniNot;
            }
            
            // Notlar alanını temizle
            notlar.value = '';
            notlar.focus();
        }
        
        // Tipi değiştiğinde kapanış tarihi otomatiği
        document.addEventListener('DOMContentLoaded', function() {
            const tipiSelect = document.querySelector('select[name="tipi"]');
            const kapanisTarihiInput = document.querySelector('input[name="kapanis_tarihi"]');
            
            if (tipiSelect && kapanisTarihiInput) {
                tipiSelect.addEventListener('change', function() {
                    const tipi = this.value;
                    
                    // Eğer Kazanıldı, Kaybedildi veya Vazgeçildi seçildiyse ve kapanış tarihi boşsa
                    if ((tipi === 'Kazanıldı' || tipi === 'Kaybedildi' || tipi === 'Vazgeçildi') && !kapanisTarihiInput.value) {
                        // Bugünün tarihini set et
                        const today = new Date().toISOString().split('T')[0];
                        kapanisTarihiInput.value = today;
                    }
                });
            }
        });
    </script>
</body>
</html>