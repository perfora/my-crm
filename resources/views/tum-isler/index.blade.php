<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tüm İşler - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        .scroll-sync {
            overflow-x: auto;
        }
        .sortable {
            cursor: pointer;
            user-select: none;
        }
        .sortable:hover {
            background-color: #f3f4f6;
        }
        .editable-cell, .editable-select, .editable-number, .editable-date {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .editable-cell:hover, .editable-select:hover, .editable-number:hover, .editable-date:hover {
            background-color: #fef3c7 !important;
        }
        .editing {
            padding: 0 !important;
        }
    </style>
</head>
<body class="bg-gray-100">
    @include('layouts.nav')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Tüm İşler</h1>
            @php
                $notionCount = \App\Models\TumIsler::whereNotNull('notion_id')->count();
            @endphp
            @if($notionCount > 0)
                <div class="bg-purple-100 text-purple-800 px-4 py-2 rounded-lg text-sm">
                    🔗 <strong>{{ $notionCount }}</strong> kayıt Notion'dan senkronize
                </div>
            @endif
        </div>
        
        @if(session('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif
        <!-- Form -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 flex justify-between items-center cursor-pointer" onclick="toggleForm()">
                <h2 class="text-xl font-bold">Yeni İş Ekle</h2>
                <span id="form-toggle-icon" class="text-2xl transform transition-transform">▼</span>
            </div>
            <div id="is-ekle-form" style="display: none;">
                <form method="POST" action="/tum-isler" class="space-y-4 px-6 pb-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">İş Adı *</label>
                        <input type="text" name="name" required class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Müşteri</label>
                        <select name="musteri_id" id="musteri-select" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            @php
                                $musteriler = \App\Models\Musteri::orderBy('sirket')->get();
                            @endphp
                            @foreach($musteriler as $musteri)
                                <option value="{{ $musteri->id }}">{{ $musteri->sirket }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Marka</label>
                        <select name="marka_id" id="marka-select" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            @php
                                $markalar = \App\Models\Marka::orderBy('name')->get();
                            @endphp
                            @foreach($markalar as $marka)
                                <option value="{{ $marka->id }}">{{ $marka->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipi</label>
                        <select name="tipi" id="tipi" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="Verilecek">Verilecek</option>
                            <option value="Verildi">Verildi</option>
                            <option value="Takip Edilecek">Takip Edilecek</option>
                            <option value="Kazanıldı">Kazanıldı</option>
                            <option value="Kaybedildi">Kaybedildi</option>
                            <option value="Vazgeçildi">Vazgeçildi</option>
                            <option value="Tamamlandı">Tamamlandı</option>
                            <option value="Askıda">Askıda</option>
                            <option value="Register">Register</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Türü</label>
                        <select name="turu" id="turu-select" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="Cihaz">Cihaz</option>
                            <option value="Yazılım ve Lisans">Yazılım ve Lisans</option>
                            <option value="Cihaz ve Lisans">Cihaz ve Lisans</option>
                            <option value="Yenileme">Yenileme</option>
                            <option value="Destek">Destek</option>
                            <option value="Hizmet Alımı">Hizmet Alımı</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Öncelik</label>
                        <select name="oncelik" id="oncelik-select" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="1">1 (Yüksek)</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4 (Düşük)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Teklif Tutarı</label>
                        <input type="number" step="0.01" name="teklif_tutari" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Alış Tutarı</label>
                        <input type="number" step="0.01" name="alis_tutari" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Kur</label>
                        <input type="number" step="0.0001" name="kur" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Açılış Tarihi</label>
                        <input type="date" name="is_guncellenme_tarihi" value="{{ date('Y-m-d') }}" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Kapanış Tarihi</label>
                        <input type="date" name="kapanis_tarihi" id="kapanis_tarihi" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Lisans Bitiş</label>
                        <input type="date" name="lisans_bitis" class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Açıklama</label>
                    <textarea name="aciklama" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Kaybedilme Nedeni</label>
                        <select name="kaybedilme_nedeni" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="Diğer">Diğer</option>
                            <option value="Bütçe Yok">Bütçe Yok</option>
                            <option value="Kendileri Kurdu">Kendileri Kurdu</option>
                            <option value="Müşteri Vazgeçti">Müşteri Vazgeçti</option>
                            <option value="Yerli Ürün Tercihi">Yerli Ürün Tercihi</option>
                            <option value="Vade/Ödeme Koşulu">Vade/Ödeme Koşulu</option>
                            <option value="Stok Yok">Stok Yok</option>
                            <option value="Rakip Daha Ucuz">Rakip Daha Ucuz</option>
                            <option value="Fiyat Yüksek">Fiyat Yüksek</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Register Durum</label>
                        <select name="register_durum" id="register-durum-select" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="Açık">Açık</option>
                            <option value="Uzatım İstendi">Uzatım İstendi</option>
                            <option value="Uzatıldı">Uzatıldı</option>
                            <option value="Kapatıldı">Kapatıldı</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Notlar</label>
                    <textarea name="notlar" rows="3" class="w-full border rounded px-3 py-2"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Geçmiş Notlar</label>
                    <textarea name="gecmis_notlar" rows="3" class="w-full border rounded px-3 py-2"></textarea>
                </div>
                
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    İş Ekle
                </button>
            </form>
            </div>
        </div>

        <!-- Kayıtlı Filtreler - Her zaman görünür -->
        <div class="bg-white rounded-lg shadow mb-4 p-4">
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-600">Kayıtlı Filtreler:</label>
                <div id="savedFiltersButtons" class="flex gap-1.5 flex-wrap flex-1">
                    <!-- JavaScript ile doldurulacak -->
                </div>
            </div>
        </div>

        <!-- Filtreler -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 flex justify-between items-center cursor-pointer" onclick="toggleFilters()">
                <h2 class="text-xl font-bold">Filtreler</h2>
                <span id="filter-toggle-icon" class="text-2xl transform transition-transform">▼</span>
            </div>
            <div id="filters-form" style="display: none;">
                <form method="GET" action="/tum-isler" id="filterForm" class="space-y-4 px-6 pb-6">
                
                <!-- Yeni Filtre Kaydet -->
                <div class="bg-green-50 p-3 rounded border border-green-200 mb-4">
                    <div class="flex gap-2 items-end flex-wrap">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Yeni Filtre Adı</label>
                            <input type="text" id="filterName" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm" placeholder="Filtre adı girin...">
                        </div>
                        <button type="button" onclick="saveCurrentFilter()" class="bg-green-500 text-white px-3 py-1.5 rounded text-sm hover:bg-green-600">
                            💾 Kaydet
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">İş Adı</label>
                        <input type="text" name="name" value="{{ request('name') }}" class="w-full border rounded px-3 py-2" placeholder="İş adında ara...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Yıl</label>
                        <select name="yil" class="w-full border rounded px-3 py-2">
                            <option value="">Tümü</option>
                            <option value="2024" {{ request('yil') == '2024' ? 'selected' : '' }}>2024</option>
                            <option value="2025" {{ request('yil') == '2025' ? 'selected' : '' }}>2025</option>
                            <option value="2026" {{ request('yil') == '2026' ? 'selected' : '' }}>2026</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipi</label>
                        <select name="tipi" class="w-full border rounded px-3 py-2">
                            <option value="">Tümü</option>
                            <option value="Kazanıldı" {{ request('tipi') == 'Kazanıldı' ? 'selected' : '' }}>Kazanıldı</option>
                            <option value="Kaybedildi" {{ request('tipi') == 'Kaybedildi' ? 'selected' : '' }}>Kaybedildi</option>
                            <option value="Verildi" {{ request('tipi') == 'Verildi' ? 'selected' : '' }}>Verildi</option>
                            <option value="Verilecek" {{ request('tipi') == 'Verilecek' ? 'selected' : '' }}>Verilecek</option>
                            <option value="Takip Edilecek" {{ request('tipi') == 'Takip Edilecek' ? 'selected' : '' }}>Takip Edilecek</option>
                            <option value="Askıda" {{ request('tipi') == 'Askıda' ? 'selected' : '' }}>Askıda</option>
                            <option value="Vazgeçildi" {{ request('tipi') == 'Vazgeçildi' ? 'selected' : '' }}>Vazgeçildi</option>
                            <option value="Tamamlandı" {{ request('tipi') == 'Tamamlandı' ? 'selected' : '' }}>Tamamlandı</option>
                            <option value="Register" {{ request('tipi') == 'Register' ? 'selected' : '' }}>Register</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Durum</label>
                        <select name="durum" class="w-full border rounded px-3 py-2">
                            <option value="">Tümü</option>
                            <option value="Aktif" {{ request('durum') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Pasif" {{ request('durum') == 'Pasif' ? 'selected' : '' }}>Pasif</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Türü</label>
                        <select name="turu" class="w-full border rounded px-3 py-2">
                            <option value="">Tümü</option>
                            <option value="Cihaz" {{ request('turu') == 'Cihaz' ? 'selected' : '' }}>Cihaz</option>
                            <option value="Yazılım ve Lisans" {{ request('turu') == 'Yazılım ve Lisans' ? 'selected' : '' }}>Yazılım ve Lisans</option>
                            <option value="Cihaz ve Lisans" {{ request('turu') == 'Cihaz ve Lisans' ? 'selected' : '' }}>Cihaz ve Lisans</option>
                            <option value="Yenileme" {{ request('turu') == 'Yenileme' ? 'selected' : '' }}>Yenileme</option>
                            <option value="Destek" {{ request('turu') == 'Destek' ? 'selected' : '' }}>Destek</option>
                            <option value="Hizmet Alımı" {{ request('turu') == 'Hizmet Alımı' ? 'selected' : '' }}>Hizmet Alımı</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Öncelik</label>
                        <select name="oncelik" class="w-full border rounded px-3 py-2">
                            <option value="">Tümü</option>
                            <option value="1" {{ request('oncelik') == '1' ? 'selected' : '' }}>1 (Yüksek)</option>
                            <option value="2" {{ request('oncelik') == '2' ? 'selected' : '' }}>2</option>
                            <option value="3" {{ request('oncelik') == '3' ? 'selected' : '' }}>3</option>
                            <option value="4" {{ request('oncelik') == '4' ? 'selected' : '' }}>4 (Düşük)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Register Durumu</label>
                        <select name="register_durum" class="w-full border rounded px-3 py-2">
                            <option value="">Tümü</option>
                            <option value="Tamam" {{ request('register_durum') == 'Tamam' ? 'selected' : '' }}>Tamam</option>
                            <option value="Beklemede" {{ request('register_durum') == 'Beklemede' ? 'selected' : '' }}>Beklemede</option>
                            <option value="Sorunlu" {{ request('register_durum') == 'Sorunlu' ? 'selected' : '' }}>Sorunlu</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Müşteri</label>
                        <select name="musteri_id" id="filter-musteri-select" class="w-full border rounded px-3 py-2">
                            <option value="">Tümü</option>
                            @foreach(\App\Models\Musteri::orderBy('sirket')->get() as $m)
                                <option value="{{ $m->id }}" {{ request('musteri_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->sirket }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Marka</label>
                        <select name="marka_id" id="filter-marka-select" class="w-full border rounded px-3 py-2">
                            <option value="">Tümü</option>
                            @foreach(\App\Models\Marka::orderBy('name')->get() as $marka)
                                <option value="{{ $marka->id }}" {{ request('marka_id') == $marka->id ? 'selected' : '' }}>
                                    {{ $marka->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Tutarlar -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Teklif (Min)</label>
                            <input type="number" step="0.01" name="teklif_min" value="{{ request('teklif_min') }}" class="w-full border rounded px-3 py-2" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Teklif (Max)</label>
                            <input type="number" step="0.01" name="teklif_max" value="{{ request('teklif_max') }}" class="w-full border rounded px-3 py-2" placeholder="∞">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Alış (Min)</label>
                            <input type="number" step="0.01" name="alis_min" value="{{ request('alis_min') }}" class="w-full border rounded px-3 py-2" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Alış (Max)</label>
                            <input type="number" step="0.01" name="alis_max" value="{{ request('alis_max') }}" class="w-full border rounded px-3 py-2" placeholder="∞">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Kar (Min)</label>
                            <input type="number" step="0.01" name="kar_min" value="{{ request('kar_min') }}" class="w-full border rounded px-3 py-2" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Kar (Max)</label>
                            <input type="number" step="0.01" name="kar_max" value="{{ request('kar_max') }}" class="w-full border rounded px-3 py-2" placeholder="∞">
                        </div>
                    </div>
                </div>
                
                <!-- Tarih Aralıkları -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Açılış (Başlangıç)</label>
                            <input type="date" name="acilis_start" value="{{ request('acilis_start') }}" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Açılış (Bitiş)</label>
                            <input type="date" name="acilis_end" value="{{ request('acilis_end') }}" class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Kapanış (Başlangıç)</label>
                            <input type="date" name="kapanis_start" value="{{ request('kapanis_start') }}" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Kapanış (Bitiş)</label>
                            <input type="date" name="kapanis_end" value="{{ request('kapanis_end') }}" class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Lisans (Başlangıç)</label>
                            <input type="date" name="lisans_start" value="{{ request('lisans_start') }}" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Lisans (Bitiş)</label>
                            <input type="date" name="lisans_end" value="{{ request('lisans_end') }}" class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Güncelleme (Başlangıç)</label>
                            <input type="date" name="updated_start" value="{{ request('updated_start') }}" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Güncelleme (Bitiş)</label>
                            <input type="date" name="updated_end" value="{{ request('updated_end') }}" class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                        🔍 Filtrele
                    </button>
                    <a href="/tum-isler" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                        🔄 Temizle
                    </a>
                </div>
            </form>
            </div>
        </div>
            
            <!-- Toplam Gösterge -->
            @php
                $query = \App\Models\TumIsler::query();
                
                // İş Adı Filtresi
                if(request('name')) {
                    $query->where('name', 'LIKE', '%' . request('name') . '%');
                }
                
                // Tipi Filtresi (önce uygula, çünkü yıl filtresi buna bağlı)
                if(request('tipi')) {
                    $query->where('tipi', request('tipi'));
                }
                
                // Yıl Filtresi - Tipi bazlı
                if(request('yil')) {
                    $tipi = request('tipi');
                    // Eğer tipi Kazanıldı veya Kaybedildi ise, yıl filtresini kapanis_tarihi'ne göre yap
                    if(in_array($tipi, ['Kazanıldı', 'Kaybedildi'])) {
                        $query->whereYear('kapanis_tarihi', request('yil'));
                    } else {
                        // Diğer durumlarda açılış tarihine göre filtrele
                        $query->whereYear('is_guncellenme_tarihi', request('yil'));
                    }
                }
                

                
                // Türü Filtresi
                if(request('turu')) {
                    $query->where('turu', request('turu'));
                }
                
                // Öncelik Filtresi
                if(request('oncelik')) {
                    $query->where('oncelik', request('oncelik'));
                }
                
                // Register Durumu Filtresi
                if(request('register_durum')) {
                    $query->where('register_durum', request('register_durum'));
                }
                
                // Müşteri Filtresi
                if(request('musteri_id')) {
                    $query->where('musteri_id', request('musteri_id'));
                }
                
                // Marka Filtresi
                if(request('marka_id')) {
                    $query->where('marka_id', request('marka_id'));
                }
                
                // Teklif Tutarı Aralığı
                if(request('teklif_min')) {
                    $query->where('teklif_tutari', '>=', request('teklif_min'));
                }
                if(request('teklif_max')) {
                    $query->where('teklif_tutari', '<=', request('teklif_max'));
                }
                
                // Alış Tutarı Aralığı
                if(request('alis_min')) {
                    $query->where('alis_tutari', '>=', request('alis_min'));
                }
                if(request('alis_max')) {
                    $query->where('alis_tutari', '<=', request('alis_max'));
                }
                
                // Kar Aralığı (Computed - backend'de hesaplanması gerekiyor)
                if(request('kar_min') || request('kar_max')) {
                    $filtered = $query->get()->filter(function($is) {
                        $kar = ($is->teklif_tutari ?? 0) - ($is->alis_tutari ?? 0);
                        $minOk = !request('kar_min') || $kar >= request('kar_min');
                        $maxOk = !request('kar_max') || $kar <= request('kar_max');
                        return $minOk && $maxOk;
                    });
                    $filtreliIsler = $filtered;
                } else {
                    // Açılış Tarihi Aralığı
                    if(request('acilis_start')) {
                        $query->whereDate('is_guncellenme_tarihi', '>=', request('acilis_start'));
                    }
                    if(request('acilis_end')) {
                        $query->whereDate('is_guncellenme_tarihi', '<=', request('acilis_end'));
                    }
                    
                    // Kapanış Tarihi Aralığı
                    if(request('kapanis_start')) {
                        $query->whereDate('kapanis_tarihi', '>=', request('kapanis_start'));
                    }
                    if(request('kapanis_end')) {
                        $query->whereDate('kapanis_tarihi', '<=', request('kapanis_end'));
                    }
                    
                    // Lisans Bitiş Aralığı
                    if(request('lisans_start')) {
                        $query->whereDate('lisans_bitis', '>=', request('lisans_start'));
                    }
                    if(request('lisans_end')) {
                        $query->whereDate('lisans_bitis', '<=', request('lisans_end'));
                    }
                    
                    // Güncelleme Tarihi Aralığı
                    if(request('updated_start')) {
                        $query->whereDate('updated_at', '>=', request('updated_start'));
                    }
                    if(request('updated_end')) {
                        $query->whereDate('updated_at', '<=', request('updated_end'));
                    }
                    
                    $filtreliIsler = $query->get();
                }

                // Para birimine göre ayrı toplamlar
                // Eğer teklif_doviz USD ise ve alis_doviz boşsa, alis'i de USD kabul et
                $toplamUSDTeklif = $filtreliIsler->filter(function($i){ 
                    return $i->teklif_doviz === 'USD'; 
                })->sum('teklif_tutari');
                
                $toplamUSDAlis = $filtreliIsler->filter(function($i){ 
                    // Eğer alis_doviz USD ise veya boş ama teklif_doviz USD ise
                    return $i->alis_doviz === 'USD' || (empty($i->alis_doviz) && $i->teklif_doviz === 'USD'); 
                })->sum('alis_tutari');
                
                $toplamKarUSD = $toplamUSDTeklif - $toplamUSDAlis;

                // TL hesaplamaları - teklif_doviz boş veya TL ise
                $toplamTLTeklif = $filtreliIsler->filter(function($i){ 
                    return empty($i->teklif_doviz) || $i->teklif_doviz === 'TL'; 
                })->sum('teklif_tutari');
                
                $toplamTLAlis = $filtreliIsler->filter(function($i){ 
                    return ($i->alis_doviz === 'TL') || (empty($i->alis_doviz) && (empty($i->teklif_doviz) || $i->teklif_doviz === 'TL')); 
                })->sum('alis_tutari');
                
                $toplamKarTL = $toplamTLTeklif - $toplamTLAlis;

                // Orijinal döviz bilgilerini topla (aciklama alanındaki [ORJ: ...] notlarından) — bilgilendirme amaçlı
                $orjCount = 0;
                $orjTeklifSum = 0;
                $orjAlisSum = 0;
                $normalize = function($s) {
                    $s = trim((string)$s);
                    if ($s === '') return 0.0;
                    if (strpos($s, ',') !== false) {
                        $s = str_replace('.', '', $s);
                        $s = str_replace(',', '.', $s);
                    } else {
                        $s = str_replace(',', '.', $s);
                    }
                    return (float)$s;
                };
                foreach ($filtreliIsler as $fi) {
                    if (!empty($fi->aciklama) && preg_match('/\[ORJ:\s*teklif\s*([0-9.,\-]+)\s*USD(?:,\s*alis\s*([0-9.,\-]+)\s*USD)?(?:,\s*kur\s*([0-9.,]+))?/i', $fi->aciklama, $m)) {
                        $orjCount++;
                        $orjTeklifSum += $normalize($m[1]);
                        if (isset($m[2]) && $m[2] !== '') {
                            $orjAlisSum += $normalize($m[2]);
                        }
                    }
                }
            @endphp
            
            @if(request()->hasAny(['yil', 'tipi', 'turu', 'musteri_id', 'marka_id', 'name', 'oncelik', 'register_durum', 'teklif_min', 'teklif_max', 'alis_min', 'alis_max', 'kar_min', 'kar_max', 'acilis_start', 'acilis_end', 'kapanis_start', 'kapanis_end', 'lisans_start', 'lisans_end', 'updated_start', 'updated_end']))
                <div class="mt-4 p-4 bg-blue-50 rounded">
                    <div class="grid grid-cols-4 gap-4 text-center">
                        <div>
                            <div class="text-sm text-gray-600">İş Sayısı</div>
                            <div class="text-2xl font-bold">{{ $filtreliIsler->count() }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Toplam Teklif (USD)</div>
                            <div class="text-2xl font-bold text-indigo-600">${{ number_format($toplamUSDTeklif, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Toplam Alış (USD)</div>
                            <div class="text-2xl font-bold text-orange-600">${{ number_format($toplamUSDAlis, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Toplam Kar (USD)</div>
                            <div class="text-2xl font-bold text-green-600">${{ number_format($toplamKarUSD, 2) }}</div>
                        </div>
                    </div>
                    @if($orjCount > 0)
                        <div class="mt-3 text-sm text-gray-700">
                            ⚠️ <strong>{{ $orjCount }}</strong> kayıtta orijinal döviz tespit edildi — toplam orijinal teklif: <strong>{{ number_format($orjTeklifSum, 2) }} USD</strong>@if($orjAlisSum > 0), orijinal alış: <strong>{{ number_format($orjAlisSum, 2) }} USD</strong>@endif.
                        </div>
                    @endif
                </div>
            @endif
        

        <!-- Liste -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Toolbar -->
            <div class="px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <!-- Sol: Aksiyon Butonları -->
                    <div class="flex items-center gap-3">
                        <button onclick="addNewRow()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex items-center gap-2 transition">
                            ➕ Ekle
                        </button>
                        <button onclick="duplicateSelected()" id="btn-duplicate" disabled class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded flex items-center gap-2 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            📋 Kopyala
                        </button>
                        <button onclick="deleteSelected()" id="btn-delete" disabled class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded flex items-center gap-2 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            🗑️ Sil
                        </button>
                        <span id="selection-count" class="text-sm text-gray-600"></span>
                    </div>
                    
                    <!-- Sağ: Sütun Seçici -->
                    <div class="relative inline-block">
                        <button id="column-toggle-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded flex items-center gap-2">
                            <span>📊 Sütunlar</span>
                            <span id="column-arrow">▼</span>
                        </button>
                        <div id="column-menu" class="hidden absolute right-0 mt-2 w-56 bg-white border rounded-lg shadow-lg z-50 p-3 max-h-96 overflow-y-auto">
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="name" checked> İş Adı
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="musteri" checked> Müşteri
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="marka" checked> Marka
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="tipi" checked> Tipi
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="durum"> Durum
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="turu"> Türü
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="oncelik" checked> Öncelik
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="kapanis_tarihi" checked> Kapanış
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="lisans_bitis"> Lisans Bitiş
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="teklif_tutari" checked> Teklif
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="alis_tutari"> Alış
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="kar_tutari"> Kar
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="is_guncellenme_tarihi" checked> Açılış
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="updated_at"> Güncelleme
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Üst scroll bar -->
            <div id="scroll-top" class="scroll-sync" style="overflow-x: auto; height: 20px;">
                <div id="scroll-content-top" style="height: 1px;"></div>
            </div>
            
            <div id="scroll-bottom" class="scroll-sync" style="overflow-x: auto;">
                <table id="isler-table" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-center">
                                <input type="checkbox" id="select-all" class="cursor-pointer">
                            </th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="name">İş Adı <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="musteri">Müşteri <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="marka">Marka <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="tipi">Tipi <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="durum">Durum <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="turu">Türü <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="oncelik">Öncelik <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="kapanis_tarihi">Kapanış <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="lisans_bitis">Lisans <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="teklif_tutari">Teklif <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="alis_tutari">Alış <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="kar_tutari">Kar <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="is_guncellenme_tarihi">Açılış <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="updated_at">Güncelleme <span class="sort-icon"></span></th>
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Yenileme</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $query = \App\Models\TumIsler::with(['musteri', 'marka']);
                            
                            // İş Adı Filtresi
                            if(request('name')) {
                                $query->where('name', 'LIKE', '%' . request('name') . '%');
                            }
                            
                            // Tipi filtresi önce (yıl filtresi buna bağlı)
                            if(request('tipi')) {
                                $query->where('tipi', request('tipi'));
                            }
                            
                            // Yıl Filtresi - Tipi bazlı
                            if(request('yil')) {
                                $tipi = request('tipi');
                                // Eğer tipi Kazanıldı veya Kaybedildi ise, yıl filtresini kapanis_tarihi'ne göre yap
                                if(in_array($tipi, ['Kazanıldı', 'Kaybedildi'])) {
                                    $query->whereYear('kapanis_tarihi', request('yil'));
                                } else {
                                    // Diğer durumlarda açılış tarihine göre filtrele
                                    $query->whereYear('is_guncellenme_tarihi', request('yil'));
                                }
                            }
                            if(request('durum')) {
                                $query->where('durum', request('durum'));
                            }
                            if(request('turu')) {
                                $query->where('turu', request('turu'));
                            }
                            if(request('oncelik')) {
                                $query->where('oncelik', request('oncelik'));
                            }
                            if(request('register_durum')) {
                                $query->where('register_durum', request('register_durum'));
                            }
                            if(request('musteri_id')) {
                                $query->where('musteri_id', request('musteri_id'));
                            }
                            if(request('marka_id')) {
                                $query->where('marka_id', request('marka_id'));
                            }
                            if(request('teklif_min')) {
                                $query->where('teklif_tutari', '>=', request('teklif_min'));
                            }
                            if(request('teklif_max')) {
                                $query->where('teklif_tutari', '<=', request('teklif_max'));
                            }
                            if(request('alis_min')) {
                                $query->where('alis_tutari', '>=', request('alis_min'));
                            }
                            if(request('alis_max')) {
                                $query->where('alis_tutari', '<=', request('alis_max'));
                            }
                            
                            // Tarih aralığı filtreleri
                            if(request('kar_min') || request('kar_max')) {
                                $filtered = $query->get()->filter(function($is) {
                                    $kar = ($is->teklif_tutari ?? 0) - ($is->alis_tutari ?? 0);
                                    $minOk = !request('kar_min') || $kar >= request('kar_min');
                                    $maxOk = !request('kar_max') || $kar <= request('kar_max');
                                    return $minOk && $maxOk;
                                });
                                $isler = $filtered->sortByDesc('is_guncellenme_tarihi')->values();
                            } else {
                                if(request('acilis_start')) {
                                    $query->whereDate('is_guncellenme_tarihi', '>=', request('acilis_start'));
                                }
                                if(request('acilis_end')) {
                                    $query->whereDate('is_guncellenme_tarihi', '<=', request('acilis_end'));
                                }
                                if(request('kapanis_start')) {
                                    $query->whereDate('kapanis_tarihi', '>=', request('kapanis_start'));
                                }
                                if(request('kapanis_end')) {
                                    $query->whereDate('kapanis_tarihi', '<=', request('kapanis_end'));
                                }
                                if(request('lisans_start')) {
                                    $query->whereDate('lisans_bitis', '>=', request('lisans_start'));
                                }
                                if(request('lisans_end')) {
                                    $query->whereDate('lisans_bitis', '<=', request('lisans_end'));
                                }
                                if(request('updated_start')) {
                                    $query->whereDate('updated_at', '>=', request('updated_start'));
                                }
                                if(request('updated_end')) {
                                    $query->whereDate('updated_at', '<=', request('updated_end'));
                                }
                                
                                // En son açılan işler üstte (açılış tarihine göre)
                                $isler = $query->orderBy('is_guncellenme_tarihi', 'DESC')->get();
                            }
                        @endphp
                        
                        @forelse($isler as $is)
                            <tr data-name="{{ $is->name }}" 
                                data-musteri="{{ $is->musteri ? $is->musteri->sirket : '' }}" 
                                data-marka="{{ $is->marka ? $is->marka->name : '' }}" 
                                data-tipi="{{ $is->tipi ?? '' }}" 
                                data-durum="{{ $is->durum ?? '' }}" 
                                data-turu="{{ $is->turu ?? '' }}" 
                                data-oncelik="{{ $is->oncelik ?? '' }}" 
                                data-kapanis_tarihi="{{ $is->kapanis_tarihi ?? '' }}" 
                                data-lisans_bitis="{{ $is->lisans_bitis ?? '' }}" 
                                data-teklif_tutari="{{ $is->teklif_tutari ?? 0 }}" 
                                data-alis_tutari="{{ $is->alis_tutari ?? 0 }}" 
                                data-kar_tutari="{{ $is->kar_tutari ?? 0 }}" 
                                data-is_guncellenme_tarihi="{{ $is->is_guncellenme_tarihi }}">
                                <td class="px-3 py-3 text-center">
                                    <input type="checkbox" class="row-checkbox cursor-pointer" data-id="{{ $is->id }}">
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap editable-cell" data-field="name" data-id="{{ $is->id }}" data-value="{{ $is->name }}">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $is->name }}</span>
                                        @if($is->notion_id)
                                            <a href="{{ $is->notion_url }}" target="_blank" 
                                               class="text-xs px-2 py-1 bg-purple-100 text-purple-700 rounded-full hover:bg-purple-200 transition"
                                               title="Notion'da aç">
                                                🔗 Notion
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap editable-select" data-field="musteri_id" data-id="{{ $is->id }}" data-value="{{ $is->musteri_id }}">
                                    @if($is->musteri)
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ $is->musteri->sirket }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap editable-select" data-field="marka_id" data-id="{{ $is->id }}" data-value="{{ $is->marka_id }}">
                                    @if($is->marka)
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                            {{ $is->marka->name }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap editable-select" data-field="tipi" data-id="{{ $is->id }}" data-value="{{ $is->tipi }}">
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        {{ $is->tipi ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @if($is->durum)
                                        {{ $is->durum }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap editable-select" data-field="turu" data-id="{{ $is->id }}" data-value="{{ $is->turu }}">{{ $is->turu ?? '-' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap editable-select" data-field="oncelik" data-id="{{ $is->id }}" data-value="{{ $is->oncelik }}">
                                    @if($is->oncelik)
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($is->oncelik == '1') bg-red-100 text-red-800
                                            @elseif($is->oncelik == '2') bg-yellow-100 text-yellow-800
                                            @elseif($is->oncelik == '3') bg-green-100 text-green-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $is->oncelik }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-sm editable-date" data-field="kapanis_tarihi" data-id="{{ $is->id }}" data-value="{{ $is->kapanis_tarihi }}">
                                    {{ $is->kapanis_tarihi ? \Carbon\Carbon::parse($is->kapanis_tarihi)->format('d.m.Y') : '-' }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-sm editable-date" data-field="lisans_bitis" data-id="{{ $is->id }}" data-value="{{ $is->lisans_bitis }}">
                                    {{ $is->lisans_bitis ? \Carbon\Carbon::parse($is->lisans_bitis)->format('d.m.Y') : '-' }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap editable-number" data-field="teklif_tutari" data-id="{{ $is->id }}" data-value="{{ $is->teklif_tutari }}">
                                    @if($is->teklif_tutari !== null)
                                        @if($is->teklif_doviz === 'USD')
                                            ${{ number_format($is->teklif_tutari, 2) }}
                                        @else
                                            {{ number_format($is->teklif_tutari, 2) }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap editable-number" data-field="alis_tutari" data-id="{{ $is->id }}" data-value="{{ $is->alis_tutari }}">
                                    @if($is->alis_tutari !== null)
                                        @if($is->alis_doviz === 'USD')
                                            ${{ number_format($is->alis_tutari, 2) }}
                                        @else
                                            {{ number_format($is->alis_tutari, 2) }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @if($is->kar_tutari)
                                        @php
                                            $isUsd = ($is->teklif_doviz === 'USD' || $is->alis_doviz === 'USD');
                                        @endphp
                                        <span class="font-semibold {{ $is->kar_tutari > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            @if($isUsd)
                                                ${{ number_format($is->kar_tutari, 2) }}
                                            @else
                                                {{ number_format($is->kar_tutari, 2) }}
                                            @endif
                                            ({{ number_format($is->kar_orani, 1) }}%)
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 editable-date" data-field="is_guncellenme_tarihi" data-id="{{ $is->id }}" data-value="{{ $is->is_guncellenme_tarihi }}">
                                    {{ $is->is_guncellenme_tarihi ? \Carbon\Carbon::parse($is->is_guncellenme_tarihi)->format('d.m.Y') : '-' }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $is->updated_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-center">
                                    @if($is->tipi === 'Kazanıldı' && $is->lisans_bitis)
                                        @php
                                            // Aynı müşteri + marka + lisans_bitis için 2026'da yenileme var mı?
                                            $yenilemeVarMi = \App\Models\TumIsler::where('musteri_id', $is->musteri_id)
                                                ->where('marka_id', $is->marka_id)
                                                ->where('lisans_bitis', $is->lisans_bitis)
                                                ->whereYear('is_guncellenme_tarihi', 2026)
                                                ->whereIn('tipi', ['Verilecek', 'Verildi', 'Takip Edilecek'])
                                                ->exists();
                                        @endphp
                                        @if(!$yenilemeVarMi)
                                            <button 
                                                onclick="yenilemeAcTumIsler({{ $is->id }})"
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium transition">
                                                🔄 Yenile
                                            </button>
                                        @else
                                            <span class="text-green-600 text-xs">✓ Açıldı</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="16" class="px-3 py-3 text-center text-gray-500">
                                    Henüz iş kaydı yok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
    </div> <!-- max-w-7xl bitiş -->

    <script>
        $(document).ready(function() {
            // Select2 başlat
            $('#musteri-select, #filter-musteri-select').select2({
                placeholder: 'Müşteri ara...',
                allowClear: true,
                language: {
                    noResults: function() {
                        return 'Sonuç bulunamadı';
                    },
                    searching: function() {
                        return 'Aranıyor...';
                    }
                }
            });
            
            $('#marka-select, #filter-marka-select').select2({
                placeholder: 'Marka ara...',
                allowClear: true,
                language: {
                    noResults: function() {
                        return 'Sonuç bulunamadı';
                    },
                    searching: function() {
                        return 'Aranıyor...';
                    }
                }
            });
            
            $('#turu-select, #oncelik-select, #register-durum-select').select2({
                placeholder: 'Seçiniz...',
                allowClear: true,
                language: {
                    noResults: function() {
                        return 'Sonuç bulunamadı';
                    }
                }
            });

            // Scroll senkronizasyonu
            const scrollTop = document.getElementById('scroll-top');
            const scrollBottom = document.getElementById('scroll-bottom');
            const table = document.getElementById('isler-table');
            
            // Üst scroll bar genişliğini ayarla
            document.getElementById('scroll-content-top').style.width = table.offsetWidth + 'px';
            
            // Scroll senkronize et
            scrollTop.addEventListener('scroll', function() {
                scrollBottom.scrollLeft = scrollTop.scrollLeft;
            });
            
            scrollBottom.addEventListener('scroll', function() {
                scrollTop.scrollLeft = scrollBottom.scrollLeft;
            });

            // Sıralama fonksiyonu
            let sortDirection = {};
            
            document.querySelectorAll('.sortable').forEach(header => {
                header.addEventListener('click', function() {
                    const column = this.getAttribute('data-column');
                    const tbody = document.querySelector('#isler-table tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr:not(:last-child)'));
                    
                    // Sıralama yönünü belirle
                    if (!sortDirection[column]) {
                        sortDirection[column] = 'asc';
                    } else {
                        sortDirection[column] = sortDirection[column] === 'asc' ? 'desc' : 'asc';
                    }
                    
                    const isAsc = sortDirection[column] === 'asc';
                    
                    // İkonları güncelle
                    document.querySelectorAll('.sort-icon').forEach(icon => icon.textContent = '');
                    this.querySelector('.sort-icon').textContent = isAsc ? ' ▲' : ' ▼';
                    
                    // Satırları sırala
                    rows.sort((a, b) => {
                        let aVal = a.getAttribute('data-' + column) || '';
                        let bVal = b.getAttribute('data-' + column) || '';
                        
                        // Sayısal sütunlar için
                        if (['oncelik', 'teklif_tutari', 'alis_tutari', 'kar_tutari'].includes(column)) {
                            aVal = parseFloat(aVal) || 0;
                            bVal = parseFloat(bVal) || 0;
                            return isAsc ? aVal - bVal : bVal - aVal;
                        }
                        
                        // Tarih sütunları için
                        if (['kapanis_tarihi', 'lisans_bitis', 'is_guncellenme_tarihi', 'updated_at'].includes(column)) {
                            aVal = aVal ? new Date(aVal) : new Date(0);
                            bVal = bVal ? new Date(bVal) : new Date(0);
                            return isAsc ? aVal - bVal : bVal - aVal;
                        }
                        
                        // Text sütunlar için
                        return isAsc ? 
                            aVal.localeCompare(bVal, 'tr') : 
                            bVal.localeCompare(aVal, 'tr');
                    });
                    
                    // Sıralanmış satırları tekrar ekle
                    rows.forEach(row => tbody.appendChild(row));
                });
            });

            // Sayfa yüklendiğinde scroll genişliğini tekrar ayarla
            window.addEventListener('load', function() {
                document.getElementById('scroll-content-top').style.width = table.offsetWidth + 'px';
            });
        });

        // Form ve filtre toggle fonksiyonları
        function toggleForm() {
            const form = document.getElementById('is-ekle-form');
            const icon = document.getElementById('form-toggle-icon');
            
            if (form && icon) {
                if (form.style.display === 'none') {
                    form.style.display = 'block';
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    form.style.display = 'none';
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        }
        
        function toggleFilters() {
            const filters = document.getElementById('filters-form');
            const icon = document.getElementById('filter-toggle-icon');
            
            if (filters && icon) {
                if (filters.style.display === 'none') {
                    filters.style.display = 'block';
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    filters.style.display = 'none';
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        }
        
        // Tipi değiştiğinde kapanış tarihi otomatiği
        $(document).ready(function() {
            // ============================================
            // CHECKBOX SEÇİM YÖNETİMİ
            // ============================================
            let selectedIds = [];
            
            // Tümünü seç/bırak
            $('#select-all').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox').prop('checked', isChecked);
                updateSelection();
            });
            
            // Tek checkbox değişikliği
            $(document).on('change', '.row-checkbox', function() {
                updateSelection();
                
                // Tümünü seç checkbox'ını güncelle
                const totalCheckboxes = $('.row-checkbox').length;
                const checkedCheckboxes = $('.row-checkbox:checked').length;
                $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
            });
            
            // Seçimleri güncelle
            function updateSelection() {
                selectedIds = [];
                $('.row-checkbox:checked').each(function() {
                    selectedIds.push($(this).data('id'));
                });
                
                // Buton durumlarını güncelle
                const hasSelection = selectedIds.length > 0;
                $('#btn-duplicate').prop('disabled', !hasSelection);
                $('#btn-delete').prop('disabled', !hasSelection);
                
                // Seçim sayısını göster
                if (hasSelection) {
                    $('#selection-count').text(selectedIds.length + ' kayıt seçili');
                } else {
                    $('#selection-count').text('');
                }
            }
            
            // ============================================
            // BULK İŞLEMLER
            // ============================================
            
            // Toplu Silme
            window.deleteSelected = function() {
                if (selectedIds.length === 0) return;
                
                if (!confirm(selectedIds.length + ' kayıt silinecek. Emin misiniz?')) return;
                
                let completed = 0;
                selectedIds.forEach(id => {
                    $.ajax({
                        url: '/tum-isler/' + id,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            completed++;
                            if (completed === selectedIds.length) {
                                location.reload();
                            }
                        }
                    });
                });
            };
            
            // Toplu Kopyalama
            window.duplicateSelected = function() {
                if (selectedIds.length === 0) return;
                
                if (!confirm(selectedIds.length + ' kayıt kopyalanacak. Emin misiniz?')) return;
                
                // İlk seçili kaydı kopyala
                window.location.href = '/tum-isler/' + selectedIds[0] + '/duplicate';
            };
            
            // Yeni Satır Ekleme
            window.addNewRow = function() {
                // Seçili satır var mı kontrol et
                let insertPosition;
                if (selectedIds.length > 0) {
                    // İlk seçili satırın üstüne ekle
                    insertPosition = $('.row-checkbox[data-id="' + selectedIds[0] + '"]').closest('tr');
                } else {
                    // En üste ekle
                    insertPosition = $('#isler-table tbody tr:first');
                }
                
                const newRow = `
                    <tr class="new-row bg-yellow-50">
                        <td class="px-3 py-3 text-center">
                            <input type="checkbox" disabled class="opacity-50">
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap editable-cell" data-field="name" data-id="new" data-value="">
                            <span class="text-gray-400">İş adı giriniz...</span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap editable-select" data-field="musteri_id" data-id="new" data-value="">
                            <span class="text-gray-400">Müşteri seçiniz...</span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap editable-select" data-field="marka_id" data-id="new" data-value="">
                            <span class="text-gray-400">Marka seçiniz...</span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap editable-select" data-field="tipi" data-id="new" data-value="">
                            <span class="text-gray-400">Tip seçiniz...</span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">-</td>
                        <td class="px-3 py-3 whitespace-nowrap editable-select" data-field="turu" data-id="new" data-value="">-</td>
                        <td class="px-3 py-3 whitespace-nowrap editable-select" data-field="oncelik" data-id="new" data-value="">-</td>
                        <td class="px-3 py-3 whitespace-nowrap editable-date" data-field="kapanis_tarihi" data-id="new" data-value="">-</td>
                        <td class="px-3 py-3 whitespace-nowrap editable-date" data-field="lisans_bitis" data-id="new" data-value="">-</td>
                        <td class="px-3 py-3 whitespace-nowrap editable-number" data-field="teklif_tutari" data-id="new" data-value="">-</td>
                        <td class="px-3 py-3 whitespace-nowrap editable-number" data-field="alis_tutari" data-id="new" data-value="">-</td>
                        <td class="px-3 py-3 whitespace-nowrap">-</td>
                        <td class="px-3 py-3 whitespace-nowrap editable-date" data-field="is_guncellenme_tarihi" data-id="new" data-value="">-</td>
                        <td class="px-3 py-3 whitespace-nowrap">-</td>
                        <td class="px-3 py-3 whitespace-nowrap text-center">-</td>
                    </tr>
                `;
                
                if (insertPosition.length > 0) {
                    insertPosition.before(newRow);
                } else {
                    $('#isler-table tbody').prepend(newRow);
                }
                
                // İlk hücreye odaklan
                setTimeout(() => {
                    $('.new-row .editable-cell').first().click();
                }, 100);
            };
            
            // ============================================
            // MEVCUT KOD
            // ============================================
            
            $('#tipi').on('change', function() {
                const tipi = $(this).val();
                const kapanisTarihi = $('#kapanis_tarihi');
                
                // Eğer Kazanıldı, Kaybedildi veya Vazgeçildi seçildiyse ve kapanış tarihi boşsa
                if ((tipi === 'Kazanıldı' || tipi === 'Kaybedildi' || tipi === 'Vazgeçildi') && !kapanisTarihi.val()) {
                    // Bugünün tarihini set et
                    const today = new Date().toISOString().split('T')[0];
                    kapanisTarihi.val(today);
                }
            });
            
            // Sütun seçici toggle
            $('#column-toggle-btn').on('click', function(e) {
                e.stopPropagation();
                $('#column-menu').toggleClass('hidden');
                $('#column-arrow').text($('#column-menu').hasClass('hidden') ? '▼' : '▲');
            });
            
            // Dışarı tıklayınca kapat
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#column-toggle-btn, #column-menu').length) {
                    $('#column-menu').addClass('hidden');
                    $('#column-arrow').text('▼');
                }
            });
            
            // Sütun göster/gizle
            $('.column-toggle').on('change', function() {
                const column = $(this).data('column');
                const isChecked = $(this).is(':checked');
                const columnIndex = $('th[data-column="' + column + '"]').index();
                
                if (columnIndex >= 0) {
                    // Başlık hücresini göster/gizle
                    $('thead th').eq(columnIndex).toggle(isChecked);
                    // Tüm satırlarda o sütunu göster/gizle
                    $('tbody tr').each(function() {
                        $(this).find('td').eq(columnIndex).toggle(isChecked);
                    });
                }
                
                // localStorage'a kaydet
                saveColumnPreferences();
            });
            
            // Sayfa yüklendiğinde kaydedilmiş sütun tercihlerini yükle
            loadColumnPreferences();
        });
        
        // Sütun tercihlerini localStorage'a kaydet
        function saveColumnPreferences() {
            const preferences = {};
            $('.column-toggle').each(function() {
                const column = $(this).data('column');
                const isChecked = $(this).is(':checked');
                preferences[column] = isChecked;
            });
            localStorage.setItem('tumIslerColumnPreferences', JSON.stringify(preferences));
        }
        
        // Sütun tercihlerini localStorage'dan yükle
        function loadColumnPreferences() {
            const saved = localStorage.getItem('tumIslerColumnPreferences');
            if (!saved) {
                // İlk yükleme - checked olmayanları gizle
                $('.column-toggle').each(function() {
                    if (!$(this).is(':checked')) {
                        $(this).trigger('change');
                    }
                });
                return;
            }
            
            const preferences = JSON.parse(saved);
            $('.column-toggle').each(function() {
                const column = $(this).data('column');
                if (preferences.hasOwnProperty(column)) {
                    const shouldBeChecked = preferences[column];
                    $(this).prop('checked', shouldBeChecked);
                    
                    // Sütunu göster/gizle
                    if (!shouldBeChecked) {
                        $(this).trigger('change');
                    }
                }
            });
        }
        
        // Filtre Kaydetme ve Yükleme Sistemi (Database-backed)
        async function saveCurrentFilter() {
            const filterName = document.getElementById('filterName').value.trim();
            if (!filterName) {
                alert('Lütfen filtre adı girin!');
                return;
            }
            
            // Form verilerini topla
            const formData = {};
            const form = document.getElementById('filterForm');
            const inputs = form.querySelectorAll('input, select');
            
            inputs.forEach(input => {
                if (input.name && input.value) {
                    formData[input.name] = input.value;
                }
            });
            
            try {
                const response = await fetch('/api/saved-filters', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: filterName,
                        page: 'tum-isler',
                        filter_data: formData
                    })
                });
                
                if (response.ok) {
                    alert('✓ Filtre kaydedildi: ' + filterName);
                    document.getElementById('filterName').value = '';
                    updateFilterButtons();
                } else {
                    alert('❌ Kayıt başarısız!');
                }
            } catch (error) {
                console.error('Filter save error:', error);
                alert('❌ Bağlantı hatası!');
            }
        }
        
        async function loadFilter(filterName) {
            try {
                const response = await fetch('/api/saved-filters?page=tum-isler');
                const filters = await response.json();
                const filter = filters.find(f => f.name === filterName);
                
                if (!filter) return;
                
                const form = document.getElementById('filterForm');
                Object.keys(filter.filter_data).forEach(key => {
                    const input = form.querySelector('[name="' + key + '"]');
                    if (input) {
                        input.value = filter.filter_data[key];
                        if ($(input).hasClass('select2-hidden-accessible')) {
                            $(input).val(filter.filter_data[key]).trigger('change');
                        }
                    }
                });
                
                form.submit();
            } catch (error) {
                console.error('Filter load error:', error);
            }
        }
        
        async function deleteFilter(filterName) {
            if (!confirm('Bu filtreyi silmek istediğinize emin misiniz?\n\n' + filterName)) {
                return;
            }
            
            try {
                const response = await fetch('/api/saved-filters/' + encodeURIComponent(filterName) + '?page=tum-isler', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    alert('✓ Filtre silindi: ' + filterName);
                    updateFilterButtons();
                } else {
                    alert('❌ Silme başarısız!');
                }
            } catch (error) {
                console.error('Filter delete error:', error);
                alert('❌ Bağlantı hatası!');
            }
        }
        
        async function updateFilterButtons() {
            try {
                const response = await fetch('/api/saved-filters?page=tum-isler');
                const filters = await response.json();
                const container = document.getElementById('savedFiltersButtons');
                
                if (filters.length === 0) {
                    container.innerHTML = '<p class="text-sm text-gray-500">Henüz kayıtlı filtre yok</p>';
                    return;
                }
                
                let html = '';
                filters.forEach(filter => {
                    html += `
                        <div class="inline-flex items-center gap-0.5 bg-blue-50 hover:bg-blue-100 rounded border border-blue-200 transition-colors">
                            <button type="button" onclick="loadFilter('${filter.name}'); return false;" class="px-2 py-0.5 text-xs font-medium text-blue-700">
                                ${filter.name}
                            </button>
                            <button type="button" onclick="deleteFilter('${filter.name}'); return false;" class="px-1.5 py-0.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-r text-xs">
                                ×
                            </button>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
            } catch (error) {
                console.error('Filter buttons update error:', error);
            }
        }
        
        // Eski fonksiyonlar - geriye uyumluluk için
        function loadSelectedFilter() {
            const select = document.getElementById('savedFilters');
            if (select) {
                const filterName = select.value;
                if (filterName) loadFilter(filterName);
            }
        }
        
        function deleteSelectedFilter() {
            const select = document.getElementById('savedFilters');
            const filterName = select.value;
            
            if (!filterName) {
                alert('Lütfen bir filtre seçin!');
                return;
            }
            
            // localStorage'dan filtreleri al
            const savedFilters = JSON.parse(localStorage.getItem('tumIslerFilters') || '{}');
            const filterData = savedFilters[filterName];
            
            if (!filterData) {
                alert('Filtre bulunamadı!');
                return;
            }
            
            // Formu temizle - TÜM inputları boşalt
            const form = document.getElementById('filterForm');
            const allInputs = form.querySelectorAll('input, select');
            allInputs.forEach(input => {
                if (input.type === 'text' || input.type === 'date' || input.type === 'number') {
                    input.value = '';
                } else if (input.tagName === 'SELECT') {
                    input.value = '';
                    // Select2 kullanıyorsa trigger et
                    if ($(input).hasClass('select2-hidden-accessible')) {
                        $(input).val('').trigger('change');
                    }
                }
            });
            
            // Filtre verilerini forma yükle - SADECE filterForm içinden ara
            Object.keys(filterData).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) {
                    input.value = filterData[key];
                    
                    // Select2 kullanıyorsa trigger et
                    if ($(input).hasClass('select2-hidden-accessible')) {
                        $(input).val(filterData[key]).trigger('change');
                    }
                }
            });
            
            alert('✓ Filtre yüklendi: ' + filterName);
        }
        
        function deleteSelectedFilter() {
            const select = document.getElementById('savedFilters');
            const filterName = select.value;
            
            if (!filterName) {
                alert('Lütfen bir filtre seçin!');
                return;
            }
            
            if (!confirm('Bu filtreyi silmek istediğinizden emin misiniz?\n\n' + filterName)) {
                return;
            }
            
            // localStorage'dan filtreleri al
            let savedFilters = JSON.parse(localStorage.getItem('tumIslerFilters') || '{}');
            
            // Filtreyi sil
            delete savedFilters[filterName];
            
            // localStorage'a kaydet
            localStorage.setItem('tumIslerFilters', JSON.stringify(savedFilters));
            
            // Dropdown'ı güncelle
            updateFilterDropdown();
            
            alert('✓ Filtre silindi: ' + filterName);
        }
        
        function updateFilterDropdown() {
            // Artık dropdown yok, sadece butonları güncelle
            updateFilterButtons();
        }
        
        // localStorage'dan Database'e otomatik migration
        async function migrateFiltersToDatabase() {
            const localFilters = JSON.parse(localStorage.getItem('tumIslerFilters') || '{}');
            
            if (Object.keys(localFilters).length === 0) {
                return; // Taşınacak filtre yok
            }
            
            console.log('📦 localStorage\'da ' + Object.keys(localFilters).length + ' filtre bulundu, database\'e taşınıyor...');
            
            let successCount = 0;
            
            for (const [filterName, filterData] of Object.entries(localFilters)) {
                try {
                    const response = await fetch('/api/saved-filters', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            name: filterName,
                            page: 'tum-isler',
                            filter_data: filterData
                        })
                    });
                    
                    if (response.ok) {
                        successCount++;
                        console.log('✓ Taşındı: ' + filterName);
                    }
                } catch (error) {
                    console.error('Taşıma hatası (' + filterName + '):', error);
                }
            }
            
            if (successCount > 0) {
                // localStorage'ı temizle (artık database'de)
                localStorage.removeItem('tumIslerFilters');
                console.log('✅ ' + successCount + ' filtre database\'e taşındı!');
                
                // Butonları güncelle
                updateFilterButtons();
            }
        }
        
        // Sayfa yüklendiğinde migration yap ve butonları doldur
        $(document).ready(function() {
            migrateFiltersToDatabase().then(() => {
                updateFilterButtons();
            });
        });
        
        // Tüm işler sayfasından yenileme kaydı aç
        function yenilemeAcTumIsler(isId) {
            if(!confirm('Bu iş için yenileme kaydı açılsın mı?')) {
                return;
            }
            
            const button = event.target;
            button.disabled = true;
            button.innerHTML = '⏳ Açılıyor...';
            
            $.ajax({
                url: '/api/yenileme-ac',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                },
                data: { is_id: isId },
                success: function(response) {
                    alert('✓ Yenileme kaydı oluşturuldu!\n\nYeni iş: ' + response.yeni_is.name);
                    location.reload(); // Sayfayı yenile
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'Hata oluştu!';
                    alert('❌ ' + error);
                    button.disabled = false;
                    button.innerHTML = '🔄 Yenile';
                }
            });
        }

        // jQuery AJAX Setup - CSRF Token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Inline editing - Text fields (Durum, Name)
        $(document).on('click', '.editable-cell:not(.editing)', function(e) {
            e.stopPropagation();
            const cell = $(this);
            const field = cell.data('field');
            const id = cell.data('id');
            const currentValue = cell.data('value') || '';
            
            console.log('Editable cell clicked:', field, id, currentValue);
            
            cell.addClass('editing');
            const originalContent = cell.html();
            
            cell.html(`<input type="text" class="w-full px-2 py-1 border rounded text-sm" value="${currentValue}" />`);
            const input = cell.find('input');
            input.focus();
            
            function saveEdit() {
                const newValue = input.val();
                
                // Yeni satır mı kontrol et
                if (id === 'new') {
                    // Yeni kayıt oluştur
                    $.ajax({
                        url: '/tum-isler',
                        method: 'POST',
                        data: {
                            [field]: newValue
                        },
                        success: function(response) {
                            // Sayfayı yenile
                            location.reload();
                        },
                        error: function() {
                            alert('Kayıt oluşturulamadı!');
                            cell.html(originalContent);
                            cell.removeClass('editing');
                        }
                    });
                } else {
                    // Mevcut kayıt güncelle
                    $.ajax({
                        url: '/tum-isler/' + id,
                        method: 'PUT',
                        data: {
                            [field]: newValue
                        },
                        success: function(response) {
                            cell.data('value', newValue);
                            
                            // Special handling for name field with Notion badge
                            if (field === 'name' && response.data.notion_id) {
                                const notionBadge = `<a href="${response.data.notion_url}" target="_blank" 
                                                   class="text-xs px-2 py-1 bg-purple-100 text-purple-700 rounded-full hover:bg-purple-200 transition"
                                                   title="Notion'da aç">
                                                    🔗 Notion
                                                </a>`;
                                cell.html(`<div class="flex items-center gap-2"><span>${newValue}</span>${notionBadge}</div>`);
                            } else {
                                cell.html(newValue || '-');
                            }
                            
                            cell.removeClass('editing');
                        },
                        error: function() {
                            alert('Kaydedilemedi!');
                            cell.html(originalContent);
                            cell.removeClass('editing');
                        }
                    });
                }
            }
            
            input.on('blur', saveEdit);
            input.on('keypress', function(e) {
                if (e.which === 13) { // Enter
                    saveEdit();
                }
            });
            input.on('keydown', function(e) {
                if (e.which === 27) { // Escape
                    cell.html(originalContent);
                    cell.removeClass('editing');
                }
            });
        });

        // Inline editing - Select fields (Tipi, Turu, Oncelik, Musteri, Marka)
        $(document).on('click', '.editable-select:not(.editing)', function(e) {
            e.stopPropagation();
            const cell = $(this);
            const field = cell.data('field');
            const id = cell.data('id');
            const currentValue = cell.data('value') || '';
            
            console.log('Editable select clicked:', field, id, currentValue);
            
            cell.addClass('editing');
            const originalContent = cell.html();
            
            let options = '';
            if (field === 'tipi') {
                options = '<option value="">Seçiniz</option>';
                @php
                    $isTipleri = \App\Models\IsTipi::orderBy('name')->get();
                @endphp
                @foreach($isTipleri as $tip)
                    options += `<option value="{{ $tip->name }}" ${currentValue === '{{ $tip->name }}' ? 'selected' : ''}>{{ $tip->name }}</option>`;
                @endforeach
                options += '<option value="__new__" style="color: green; font-weight: bold;">➕ Yeni Tip Ekle</option>';
            } else if (field === 'turu') {
                options = '<option value="">Seçiniz</option>';
                @php
                    $isTurleri = \App\Models\IsTuru::orderBy('name')->get();
                @endphp
                @foreach($isTurleri as $tur)
                    options += `<option value="{{ $tur->name }}" ${currentValue === '{{ $tur->name }}' ? 'selected' : ''}>{{ $tur->name }}</option>`;
                @endforeach
                options += '<option value="__new__" style="color: green; font-weight: bold;">➕ Yeni Tür Ekle</option>';
            } else if (field === 'oncelik') {
                options = '<option value="">Seçiniz</option>';
                @php
                    $oncelikler = \App\Models\Oncelik::orderBy('sira')->get();
                @endphp
                @foreach($oncelikler as $oncelik)
                    options += `<option value="{{ $oncelik->name }}" ${currentValue === '{{ $oncelik->name }}' ? 'selected' : ''}>{{ $oncelik->name }}</option>`;
                @endforeach
                options += '<option value="__new__" style="color: green; font-weight: bold;">➕ Yeni Öncelik Ekle</option>';
            } else if (field === 'musteri_id') {
                options = '<option value="">Seçiniz</option>';
                @php
                    $musteriler = \App\Models\Musteri::orderBy('sirket')->get();
                @endphp
                @foreach($musteriler as $musteri)
                    options += `<option value="{{ $musteri->id }}" ${currentValue == '{{ $musteri->id }}' ? 'selected' : ''}>{{ $musteri->sirket }}</option>`;
                @endforeach
                options += '<option value="__new__" style="color: green; font-weight: bold;">➕ Yeni Müşteri Ekle</option>';
            } else if (field === 'marka_id') {
                options = '<option value="">Seçiniz</option>';
                @php
                    $markalar = \App\Models\Marka::orderBy('name')->get();
                @endphp
                @foreach($markalar as $marka)
                    options += `<option value="{{ $marka->id }}" ${currentValue == '{{ $marka->id }}' ? 'selected' : ''}>{{ $marka->name }}</option>`;
                @endforeach
                options += '<option value="__new__" style="color: green; font-weight: bold;">➕ Yeni Marka Ekle</option>';
            }
            
            cell.html(`<select class="w-full px-2 py-1 border rounded text-sm">${options}</select>`);
            const select = cell.find('select');
            select.focus();
            
            // Yeni kayıt ekleme kontrolü
            select.on('change', function() {
                const selectedValue = $(this).val();
                
                if (selectedValue === '__new__') {
                    // Yeni kayıt ekle
                    if (field === 'marka_id') {
                        const markaAdi = prompt('Yeni marka adını giriniz:');
                        if (!markaAdi || markaAdi.trim() === '') {
                            select.val('');
                            return;
                        }
                        
                        // Marka oluştur
                        $.ajax({
                            url: '/markalar',
                            method: 'POST',
                            data: { name: markaAdi.trim() },
                            success: function(response) {
                                // Yeni markayı seç ve kaydet
                                select.find('option[value="__new__"]').before(
                                    `<option value="${response.data.id}" selected>${response.data.name}</option>`
                                );
                                select.val(response.data.id);
                                saveSelect();
                            },
                            error: function() {
                                alert('Marka eklenemedi!');
                                select.val('');
                                cell.html(originalContent);
                                cell.removeClass('editing');
                            }
                        });
                    } else if (field === 'musteri_id') {
                        const musteriAdi = prompt('Yeni müşteri/firma adını giriniz:');
                        if (!musteriAdi || musteriAdi.trim() === '') {
                            select.val('');
                            return;
                        }
                        
                        // Müşteri oluştur
                        $.ajax({
                            url: '/musteriler',
                            method: 'POST',
                            data: { sirket: musteriAdi.trim() },
                            success: function(response) {
                                // Yeni müşteriyi seç ve kaydet
                                select.find('option[value="__new__"]').before(
                                    `<option value="${response.data.id}" selected>${response.data.sirket}</option>`
                                );
                                select.val(response.data.id);
                                saveSelect();
                            },
                            error: function() {
                                alert('Müşteri eklenemedi!');
                                select.val('');
                                cell.html(originalContent);
                                cell.removeClass('editing');
                            }
                        });
                    } else if (field === 'tipi') {
                        const tipi = prompt('Yeni iş tipini giriniz:');
                        if (!tipi || tipi.trim() === '') {
                            select.val('');
                            return;
                        }
                        
                        $.ajax({
                            url: '/is-tipleri',
                            method: 'POST',
                            data: { name: tipi.trim() },
                            success: function(response) {
                                select.find('option[value="__new__"]').before(
                                    `<option value="${response.data.name}" selected>${response.data.name}</option>`
                                );
                                select.val(response.data.name);
                                saveSelect();
                            },
                            error: function() {
                                alert('İş tipi eklenemedi!');
                                select.val('');
                                cell.html(originalContent);
                                cell.removeClass('editing');
                            }
                        });
                    } else if (field === 'turu') {
                        const turu = prompt('Yeni iş türünü giriniz:');
                        if (!turu || turu.trim() === '') {
                            select.val('');
                            return;
                        }
                        
                        $.ajax({
                            url: '/is-turleri',
                            method: 'POST',
                            data: { name: turu.trim() },
                            success: function(response) {
                                select.find('option[value="__new__"]').before(
                                    `<option value="${response.data.name}" selected>${response.data.name}</option>`
                                );
                                select.val(response.data.name);
                                saveSelect();
                            },
                            error: function() {
                                alert('İş türü eklenemedi!');
                                select.val('');
                                cell.html(originalContent);
                                cell.removeClass('editing');
                            }
                        });
                    } else if (field === 'oncelik') {
                        const oncelik = prompt('Yeni öncelik giriniz:');
                        if (!oncelik || oncelik.trim() === '') {
                            select.val('');
                            return;
                        }
                        
                        $.ajax({
                            url: '/oncelikler',
                            method: 'POST',
                            data: { name: oncelik.trim() },
                            success: function(response) {
                                select.find('option[value="__new__"]').before(
                                    `<option value="${response.data.name}" selected>${response.data.name}</option>`
                                );
                                select.val(response.data.name);
                                saveSelect();
                            },
                            error: function() {
                                alert('Öncelik eklenemedi!');
                                select.val('');
                                cell.html(originalContent);
                                cell.removeClass('editing');
                            }
                        });
                    }
                } else {
                    saveSelect();
                }
            });
            
            function saveSelect() {
                const newValue = select.val();
                
                // "__new__" seçeneğiyse kaydetme (zaten yeni kayıt akışı başladı)
                if (newValue === '__new__' || !newValue) {
                    return;
                }
                
                // Yeni satır mı kontrol et
                if (id === 'new') {
                    // Yeni kayıt oluştur
                    $.ajax({
                        url: '/tum-isler',
                        method: 'POST',
                        data: {
                            [field]: newValue
                        },
                        success: function(response) {
                            // Sayfayı yenile
                            location.reload();
                        },
                        error: function() {
                            alert('Kayıt oluşturulamadı!');
                            cell.html(originalContent);
                            cell.removeClass('editing');
                        }
                    });
                } else {
                    // Mevcut kayıt güncelle
                    $.ajax({
                        url: '/tum-isler/' + id,
                        method: 'PUT',
                        data: {
                            [field]: newValue
                        },
                        success: function(response) {
                            cell.data('value', newValue);
                            
                            // Rebuild the display
                            if (field === 'tipi') {
                                cell.html(`<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">${newValue || '-'}</span>`);
                            } else if (field === 'turu') {
                                cell.html(newValue || '-');
                            } else if (field === 'oncelik') {
                                if (newValue) {
                                    let badgeClass = 'bg-gray-100 text-gray-800';
                                    if (newValue === '1') badgeClass = 'bg-red-100 text-red-800';
                                    else if (newValue === '2') badgeClass = 'bg-yellow-100 text-yellow-800';
                                    else if (newValue === '3') badgeClass = 'bg-green-100 text-green-800';
                                    cell.html(`<span class="px-2 py-1 text-xs rounded-full ${badgeClass}">${newValue}</span>`);
                                } else {
                                    cell.html('-');
                                }
                            } else if (field === 'musteri_id') {
                                if (response.data.musteri) {
                                    cell.html(`<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">${response.data.musteri.sirket}</span>`);
                                } else {
                                    cell.html('-');
                                }
                            } else if (field === 'marka_id') {
                                if (response.data.marka) {
                                    cell.html(`<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">${response.data.marka.name}</span>`);
                                } else {
                                    cell.html('-');
                                }
                            }
                            
                            cell.removeClass('editing');
                        },
                        error: function() {
                            alert('Kaydedilemedi!');
                            cell.html(originalContent);
                            cell.removeClass('editing');
                        }
                    });
                }
            }
            
            select.on('change', saveSelect);
            select.on('blur', function() {
                cell.html(originalContent);
                cell.removeClass('editing');
            });
            select.on('keydown', function(e) {
                if (e.which === 27) { // Escape
                    cell.html(originalContent);
                    cell.removeClass('editing');
                }
            });
        });

        // Inline editing - Number fields (Teklif, Alis)
        $(document).on('click', '.editable-number:not(.editing)', function(e) {
            e.stopPropagation();
            const cell = $(this);
            const field = cell.data('field');
            const id = cell.data('id');
            const currentValue = cell.data('value') || '';
            
            cell.addClass('editing');
            const originalContent = cell.html();
            
            cell.html(`<input type="number" step="0.01" class="w-full px-2 py-1 border rounded text-sm" value="${currentValue}" />`);
            const input = cell.find('input');
            input.focus();
            
            function saveNumber() {
                const newValue = input.val();
                
                $.ajax({
                    url: '/tum-isler/' + id,
                    method: 'PUT',
                    data: {
                        [field]: newValue
                    },
                    success: function(response) {
                        cell.data('value', newValue);
                        if (newValue) {
                            const formatted = parseFloat(newValue).toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            cell.html(formatted);
                        } else {
                            cell.html('-');
                        }
                        cell.removeClass('editing');
                        location.reload(); // Kar hesabını güncellemek için
                    },
                    error: function() {
                        alert('Kaydedilemedi!');
                        cell.html(originalContent);
                        cell.removeClass('editing');
                    }
                });
            }
            
            input.on('blur', saveNumber);
            input.on('keypress', function(e) {
                if (e.which === 13) saveNumber();
            });
            input.on('keydown', function(e) {
                if (e.which === 27) {
                    cell.html(originalContent);
                    cell.removeClass('editing');
                }
            });
        });

        // Inline editing - Date fields (Kapanis, Lisans, Acilis)
        $(document).on('click', '.editable-date:not(.editing)', function(e) {
            e.stopPropagation();
            const cell = $(this);
            const field = cell.data('field');
            const id = cell.data('id');
            const currentValue = cell.data('value') || '';
            
            cell.addClass('editing');
            const originalContent = cell.html();
            
            cell.html(`<input type="date" class="w-full px-2 py-1 border rounded text-sm" value="${currentValue}" />`);
            const input = cell.find('input');
            input.focus();
            
            function saveDate() {
                const newValue = input.val();
                
                $.ajax({
                    url: '/tum-isler/' + id,
                    method: 'PUT',
                    data: {
                        [field]: newValue
                    },
                    success: function(response) {
                        cell.data('value', newValue);
                        if (newValue) {
                            const date = new Date(newValue);
                            const formatted = date.toLocaleDateString('tr-TR');
                            cell.html(formatted);
                        } else {
                            cell.html('-');
                        }
                        cell.removeClass('editing');
                    },
                    error: function() {
                        alert('Kaydedilemedi!');
                        cell.html(originalContent);
                        cell.removeClass('editing');
                    }
                });
            }
            
            input.on('change', saveDate);
            input.on('blur', function() {
                setTimeout(function() {
                    if (!input.is(':focus')) {
                        cell.html(originalContent);
                        cell.removeClass('editing');
                    }
                }, 200);
            });
            input.on('keydown', function(e) {
                if (e.which === 27) {
                    cell.html(originalContent);
                    cell.removeClass('editing');
                }
            });
        });
    </script>
    
    <!-- Ayrı JavaScript Dosyaları -->
    <script src="/js/tum-isler-filters.js"></script>
    <script src="/js/tum-isler-main.js"></script>
</body>
</html>