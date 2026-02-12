<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Firmalar - CRM</title>
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
        /* Inline editing Select2 max-height */
        .select2-dropdown-inline-edit .select2-results {
            max-height: 250px;
            overflow-y: auto;
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
        .editable-cell, .editable-select {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .editable-cell:hover, .editable-select:hover {
            background-color: #fef3c7 !important;
        }
        .editing {
            padding: 0 !important;
        }
        .quick-note-modal {
            position: fixed;
            inset: 0;
            background: rgba(17, 24, 39, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
    </style>
</head>
<body class="bg-gray-100">
    @include('layouts.nav')
    
    <div class="container mx-auto px-4 py-8">
        @php
            $toplamMusteri = \App\Models\Musteri::count();
            // Türü değerleri - veritabanından mevcut tüm türleri çek
            $existingTuruValues = \App\Models\Musteri::whereNotNull('turu')
                ->distinct()
                ->pluck('turu')
                ->filter()
                ->sort()
                ->values();
        @endphp
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Firmalar</h1>
            <span class="text-lg font-semibold text-gray-600">Toplam: {{ $toplamMusteri }}</span>
        </div>
        
        @if(session('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        <!-- Kayıtlı Filtreler -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <div class="flex flex-wrap items-center gap-3">
                <label class="text-sm font-medium text-gray-600">Kayıtlı Filtreler:</label>
                <div id="savedFiltersButtons" class="flex gap-1.5 flex-wrap flex-1">
                    <p class="text-sm text-gray-500">Henüz kayıtlı filtre yok</p>
                </div>
                <input type="text" id="filterName" class="border border-gray-200 rounded px-2 py-1.5 text-sm w-48" placeholder="Filtre adı">
                <button type="button" onclick="saveCurrentFilter()" class="bg-green-500 text-white px-3 py-1.5 rounded text-sm hover:bg-green-600">
                    + Kaydet
                </button>
            </div>
        </div>

        <!-- Filtreler -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 flex justify-between items-center cursor-pointer" onclick="toggleFilters()">
                <h2 class="text-xl font-bold">🔍 Filtreler</h2>
                <span id="filter-toggle-icon" class="text-2xl transform transition-transform">▼</span>
            </div>
            <div id="filtre-alani" style="display: none;">
                <form id="filterForm" class="space-y-4 px-6 pb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Şirket Adı</label>
                            <input type="text" name="sirket" id="filter-sirket" class="w-full border rounded px-3 py-2" placeholder="Ara...">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Şehir</label>
                            <input type="text" name="sehir" id="filter-sehir" class="w-full border rounded px-3 py-2" placeholder="Ara...">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Derece</label>
                            <select name="derece[]" id="filter-derece" class="w-full border rounded px-3 py-2 select2-filter" multiple>
                                <option value="1 -Sık">1 - Sık</option>
                                <option value="2 - Orta">2 - Orta</option>
                                <option value="3- Düşük">3 - Düşük</option>
                                <option value="4 - Potansiyel">4 - Potansiyel</option>
                                <option value="5 - İş Ortağı">5 - İş Ortağı</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Türü</label>
                            <select name="turu[]" id="filter-turu" class="w-full border rounded px-3 py-2 select2-filter" multiple>
                                <option value="Netcom">Netcom</option>
                                <option value="Bayi">Bayi</option>
                                <option value="Resmi Kurum">Resmi Kurum</option>
                                <option value="Üniversite">Üniversite</option>
                                <option value="Belediye">Belediye</option>
                                <option value="Hastane">Hastane</option>
                                <option value="Özel Sektör">Özel Sektör</option>
                                <option value="Tedarikçi">Tedarikçi</option>
                                <option value="Üretici">Üretici</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Arama Periyodu (Gün)</label>
                            <input type="number" min="1" name="arama_periyodu_gun" id="filter-arama-periyodu-gun" class="w-full border rounded px-3 py-2" placeholder="Örn: 30">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Ziyaret Periyodu (Gün)</label>
                            <input type="number" min="1" name="ziyaret_periyodu_gun" id="filter-ziyaret-periyodu-gun" class="w-full border rounded px-3 py-2" placeholder="Örn: 60">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Temas Türü</label>
                            <select name="temas_kurali[]" id="filter-temas-kurali" class="w-full border rounded px-3 py-2 select2-filter" multiple>
                                <option value="Arama Yeterli">Arama Yeterli</option>
                                <option value="Ziyaret Öncelikli">Ziyaret Öncelikli</option>
                                <option value="Her İkisi Zorunlu">Her İkisi Zorunlu</option>
                                <option value="Şehir Dışı (Arama Öncelikli)">Şehir Dışı (Arama Öncelikli)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="button" onclick="applyFilters()" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                            🔍 Filtrele
                        </button>
                        <button type="button" onclick="clearFilters()" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                            🔄 Temizle
                        </button>
                    </div>
                </form>
            </div>
        </div>

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
                                    <input type="checkbox" class="column-toggle" data-column="sirket" checked> Şirket
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="sehir" checked> Şehir
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="telefon" checked> Telefon
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="derece" checked> Derece
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="turu" checked> Türü
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="arama_periyodu_gun" checked> Arama Periyodu
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="ziyaret_periyodu_gun" checked> Ziyaret Periyodu
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="temas_kurali" checked> Temas Kuralı
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="adres"> Adres
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="notlar"> Notlar
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="quick_actions" checked> Hızlı
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="en_son_ziyaret" checked> Son Bağlantı
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="son_baglanti_turu" checked> Bağlantı Türü
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="ziyaret_gun" checked> Bağlantı Gün
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="ziyaret_adeti" checked> Ziyaret Adeti
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="toplam_teklif" checked> Toplam Teklif
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="kazanildi_toplami" checked> Kazanıldı
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
            
            <div id="scroll-bottom" class="scroll-sync overflow-x-auto">
                <table id="musteriler-table" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-center">
                                <input type="checkbox" id="select-all" class="cursor-pointer">
                            </th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="sirket">Şirket <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="sehir">Şehir <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="telefon">Telefon <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="derece">Derece <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="turu">Türü <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="arama_periyodu_gun">Arama Periyodu <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="ziyaret_periyodu_gun">Ziyaret Periyodu <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="temas_kurali">Temas Kuralı <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="adres">Adres <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="notlar">Notlar <span class="sort-icon"></span></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hızlı</th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="en_son_ziyaret">Son Bağlantı <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="son_baglanti_turu">Bağlantı Türü <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="ziyaret_gun">Bağlantı Gün <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="ziyaret_adeti">Ziyaret Adeti <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="toplam_teklif">Toplam Teklif <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="kazanildi_toplami">Kazanıldı <span class="sort-icon"></span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $musteriler = \App\Models\Musteri::with(['ziyaretler', 'tumIsler'])->latest()->get();
                        @endphp
                        
                        @forelse($musteriler as $musteri)
                            <tr data-sirket="{{ $musteri->sirket }}" 
                                data-sehir="{{ $musteri->sehir ?? '' }}" 
                                data-telefon="{{ $musteri->telefon ?? '' }}"
                                data-adres="{{ $musteri->adres ?? '' }}"
                                data-notlar="{{ $musteri->notlar ?? '' }}"
                                data-derece="{{ $musteri->derece ?? '' }}" 
                                data-turu="{{ $musteri->turu ?? '' }}" 
                                data-arama_periyodu_gun="{{ (int)($musteri->arama_periyodu_gun ?? 0) }}"
                                data-ziyaret_periyodu_gun="{{ (int)($musteri->ziyaret_periyodu_gun ?? 0) }}"
                                data-temas_kurali="{{ $musteri->temas_kurali ?? '' }}"
                                data-en_son_ziyaret="{{ $musteri->en_son_ziyaret ?? '' }}" 
                                data-son_baglanti_turu="{{ $musteri->son_baglanti_turu ?? '' }}"
                                data-ziyaret_gun="{{ (int)($musteri->ziyaret_gun ?? 0) }}" 
                                data-ziyaret_adeti="{{ $musteri->ziyaret_adeti }}" 
                                data-toplam_teklif="{{ $musteri->toplam_teklif }}" 
                                data-kazanildi_toplami="{{ $musteri->kazanildi_toplami }}">
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <input type="checkbox" class="row-checkbox cursor-pointer" data-id="{{ $musteri->id }}">
                                </td>
                                @php
                                    $sirketLinkClass = match($musteri->derece) {
                                        '1 -Sık' => 'text-red-700 hover:text-red-800',
                                        '2 - Orta' => 'text-orange-700 hover:text-orange-800',
                                        '3- Düşük' => 'text-green-700 hover:text-green-800',
                                        '4 - Potansiyel' => 'text-blue-700 hover:text-blue-800',
                                        '5 - İş Ortağı' => 'text-slate-700 hover:text-slate-900',
                                        default => 'text-blue-600 hover:text-blue-800',
                                    };
                                @endphp
                                <td class="px-6 py-4 whitespace-nowrap font-medium editable-cell" data-field="sirket" data-id="{{ $musteri->id }}" data-value="{{ $musteri->sirket }}">
                                    <a href="/musteriler/{{ $musteri->id }}" class="{{ $sirketLinkClass }} hover:underline">
                                        {{ $musteri->sirket }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="sehir" data-id="{{ $musteri->id }}" data-value="{{ $musteri->sehir }}">{{ $musteri->sehir ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="telefon" data-id="{{ $musteri->id }}" data-value="{{ $musteri->telefon }}">{{ $musteri->telefon ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap editable-select" data-field="derece" data-id="{{ $musteri->id }}" data-value="{{ $musteri->derece }}">
                                    @if($musteri->derece)
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($musteri->derece == '1 -Sık') bg-red-100 text-red-800
                                            @elseif($musteri->derece == '2 - Orta') bg-yellow-100 text-yellow-800
                                            @elseif($musteri->derece == '3- Düşük') bg-green-100 text-green-800
                                            @elseif($musteri->derece == '4 - Potansiyel') bg-blue-100 text-blue-800
                                            @elseif($musteri->derece == '5 - İş Ortağı') bg-slate-200 text-slate-900
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $musteri->derece }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap editable-select" data-field="turu" data-id="{{ $musteri->id }}" data-value="{{ $musteri->turu }}">
                                    @if($musteri->turu)
                                        <span class="px-2 py-1 text-xs rounded-full" data-turu-badge="{{ $musteri->turu }}">
                                            {{ $musteri->turu }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="arama_periyodu_gun" data-id="{{ $musteri->id }}" data-value="{{ $musteri->arama_periyodu_gun }}">
                                    {{ $musteri->arama_periyodu_gun ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="ziyaret_periyodu_gun" data-id="{{ $musteri->id }}" data-value="{{ $musteri->ziyaret_periyodu_gun }}">
                                    {{ $musteri->ziyaret_periyodu_gun ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap editable-select" data-field="temas_kurali" data-id="{{ $musteri->id }}" data-value="{{ $musteri->temas_kurali }}">
                                    @if($musteri->temas_kurali)
                                        <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800">{{ $musteri->temas_kurali }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="adres" data-id="{{ $musteri->id }}" data-value="{{ $musteri->adres }}">{{ $musteri->adres ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="notlar" data-id="{{ $musteri->id }}" data-value="{{ $musteri->notlar }}">{{ $musteri->notlar ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                                class="quick-contact-btn px-2 py-1 text-xs rounded bg-green-100 text-green-800 hover:bg-green-200"
                                                data-musteri-id="{{ $musteri->id }}"
                                                data-musteri-name="{{ $musteri->sirket }}"
                                                data-contact-type="Telefon"
                                                title="Hızlı Arama Kaydı">
                                            📞
                                        </button>
                                        <button type="button"
                                                class="quick-contact-btn px-2 py-1 text-xs rounded bg-purple-100 text-purple-800 hover:bg-purple-200"
                                                data-musteri-id="{{ $musteri->id }}"
                                                data-musteri-name="{{ $musteri->sirket }}"
                                                data-contact-type="Ziyaret"
                                                title="Hızlı Ziyaret Kaydı">
                                            👥
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $musteri->en_son_ziyaret ? $musteri->en_son_ziyaret->timezone(config('crm.timezone'))->format(config('crm.datetime_format')) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($musteri->son_baglanti_turu)
                                        <span class="px-2 py-1 text-xs rounded-full {{ in_array(mb_strtolower($musteri->son_baglanti_turu), ['telefon', 'arama']) ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ $musteri->son_baglanti_turu }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($musteri->ziyaret_gun !== null)
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($musteri->ziyaret_gun > 60) bg-red-100 text-red-800
                                            @elseif($musteri->ziyaret_gun > 30) bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ (int)$musteri->ziyaret_gun }} gün
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                        {{ $musteri->ziyaret_adeti }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-semibold">
                                    ${{ number_format($musteri->toplam_teklif, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-semibold text-green-600">
                                    ${{ number_format($musteri->kazanildi_toplami, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="18" class="px-6 py-4 text-center text-gray-500">
                                    Henüz müşteri kaydı yok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="quick-note-modal" class="quick-note-modal">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-5">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold">Hızlı Kayıt Notu</h3>
                <button type="button" id="quick-note-close" class="text-gray-500 hover:text-gray-800">✕</button>
            </div>
            <p class="text-sm text-gray-600 mb-3">Kayıt oluşturuldu. İstersen notu şimdi ekleyebilirsin.</p>
            <textarea id="quick-note-text" class="w-full h-40 border rounded p-3 text-sm" placeholder="Not..."></textarea>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" id="quick-note-later" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Sonra</button>
                <button type="button" id="quick-note-save" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Notu Kaydet</button>
            </div>
        </div>
    </div>

    <script src="{{ asset('public/js/crm-toolbar.js') }}"></script>
    <script>
        // Global değişkenler
        let existingTuruValues = @json($existingTuruValues);
        const quickContactLock = {};
        let quickNoteVisitId = null;
        const defaultTuruValues = ['Netcom', 'Bayi', 'Resmi Kurum', 'Üniversite', 'Belediye', 'Hastane', 'Özel Sektör', 'Tedarikçi', 'Üretici', 'Diğer'];
        
        // Renk paleti - her yeni tür için farklı renk
        const colorPalette = [
            'bg-purple-100 text-purple-800',
            'bg-pink-100 text-pink-800',
            'bg-indigo-100 text-indigo-800',
            'bg-teal-100 text-teal-800',
            'bg-orange-100 text-orange-800',
            'bg-lime-100 text-lime-800',
            'bg-cyan-100 text-cyan-800',
            'bg-rose-100 text-rose-800',
            'bg-amber-100 text-amber-800',
            'bg-emerald-100 text-emerald-800',
            'bg-sky-100 text-sky-800',
            'bg-violet-100 text-violet-800',
            'bg-fuchsia-100 text-fuchsia-800',
        ];
        
        // Her türe atanmış rengi sakla
        const turuColors = {};
        
        // Varsayılan türler için mavi renk ata
        defaultTuruValues.forEach(val => {
            turuColors[val] = 'bg-blue-100 text-blue-800';
        });
        
        // Mevcut custom türler için renk ata
        let colorIndex = 0;
        existingTuruValues.forEach(val => {
            if (!defaultTuruValues.includes(val)) {
                turuColors[val] = colorPalette[colorIndex % colorPalette.length];
                colorIndex++;
            }
        });
        
        // Yeni tür için renk al
        function getColorForTuru(turu) {
            if (turuColors[turu]) {
                return turuColors[turu];
            }
            // Yeni değer - sonraki rengi kullan
            const color = colorPalette[Object.keys(turuColors).filter(k => !defaultTuruValues.includes(k)).length % colorPalette.length];
            turuColors[turu] = color;
            return color;
        }

        function getSirketLinkColorClassByDegree(derece) {
            if (derece === '1 -Sık') return 'text-red-700 hover:text-red-800';
            if (derece === '2 - Orta') return 'text-orange-700 hover:text-orange-800';
            if (derece === '3- Düşük') return 'text-green-700 hover:text-green-800';
            if (derece === '4 - Potansiyel') return 'text-blue-700 hover:text-blue-800';
            if (derece === '5 - İş Ortağı') return 'text-slate-700 hover:text-slate-900';
            return 'text-blue-600 hover:text-blue-800';
        }

        function applySirketLinkColorByDegree(row, derece) {
            const link = row.find('td[data-field="sirket"] a');
            if (!link.length) return;
            link.removeClass('text-red-700 hover:text-red-800 text-orange-700 hover:text-orange-800 text-green-700 hover:text-green-800 text-blue-700 hover:text-blue-800 text-slate-700 hover:text-slate-900 text-blue-600 hover:text-blue-800');
            link.addClass(getSirketLinkColorClassByDegree(derece));
        }

        function deleteTuruInline(turu, afterDelete) {
            if (!turu || defaultTuruValues.includes(turu)) return;
            if (!confirm('"' + turu + '" türü silinsin mi? Bu türe sahip müşterilerde tür bilgisi boşaltılacak.')) return;

            $.ajax({
                url: '/musteriler/delete-turu',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    turu: turu
                },
                success: function() {
                    existingTuruValues = existingTuruValues.filter(t => t !== turu);
                    delete turuColors[turu];

                    $('#filter-turu option').filter(function() {
                        return $(this).val() === turu;
                    }).remove();
                    $('#filter-turu').trigger('change.select2');

                    if (typeof afterDelete === 'function') afterDelete();
                    // Satır içi edit eski badge'i geri çizebildiği için kesin görünüm için yenile
                    location.reload();
                },
                error: function(xhr) {
                    alert('Tür silinemedi! ' + (xhr.responseJSON?.message || xhr.statusText || ''));
                }
            });
        }
        
        $(document).ready(function() {
            function getSelect2Config(placeholder, extra = {}) {
                return Object.assign({
                    placeholder: placeholder,
                    allowClear: true,
                    width: '100%',
                    minimumResultsForSearch: 0,
                    language: {
                        noResults: function() { return 'Sonuç bulunamadı'; },
                        searching: function() { return 'Aranıyor...'; }
                    }
                }, extra);
            }

            // Sayfa yüklendiğinde tüm turu badge'lerine renk uygula
            $('[data-turu-badge]').each(function() {
                const turu = $(this).data('turu-badge');
                const color = getColorForTuru(turu);
                $(this).addClass(color);
            });
            
            // Select2 başlat
            $('#filter-derece, #filter-turu, #filter-temas-kurali').select2(getSelect2Config('Bir veya daha fazla seçin...', {
                closeOnSelect: false,
                placeholder: 'Seçiniz'
            }));
            updateFilterButtons();

            // Scroll senkronizasyonu
            const scrollTop = document.getElementById('scroll-top');
            const scrollBottom = document.getElementById('scroll-bottom');
            const table = document.getElementById('musteriler-table');
            
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
                    const tbody = document.querySelector('#musteriler-table tbody');
                    // Sadece veri satırlarını al; boş mesaj satırını hariç tut
                    const rows = Array.from(tbody.querySelectorAll('tr')).filter(row => !row.querySelector('td[colspan]'));
                    
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
                        if (['arama_periyodu_gun', 'ziyaret_periyodu_gun', 'ziyaret_gun', 'ziyaret_adeti', 'toplam_teklif', 'kazanildi_toplami'].includes(column)) {
                            aVal = parseFloat(aVal) || 0;
                            bVal = parseFloat(bVal) || 0;
                            return isAsc ? aVal - bVal : bVal - aVal;
                        }
                        
                        // Tarih sütunları için
                        if (['en_son_ziyaret'].includes(column)) {
                            aVal = aVal ? new Date(aVal) : new Date(0);
                            bVal = bVal ? new Date(bVal) : new Date(0);
                            return isAsc ? aVal - bVal : bVal - aVal;
                        }
                        
                        // Text sütunlar için
                        aVal = String(aVal).trim();
                        bVal = String(bVal).trim();
                        return isAsc ?
                            aVal.localeCompare(bVal, 'tr', { sensitivity: 'base' }) :
                            bVal.localeCompare(aVal, 'tr', { sensitivity: 'base' });
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

        // Filtre toggle fonksiyonu
        function toggleFilters() {
            const filters = document.getElementById('filtre-alani');
            const icon = document.getElementById('filter-toggle-icon');
            
            if (filters.style.display === 'none') {
                filters.style.display = 'block';
                icon.style.transform = 'rotate(180deg)';
            } else {
                filters.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
            }
        }

        // Filtre uygulama
        function applyFilters() {
            const sirket = document.getElementById('filter-sirket').value.toLowerCase();
            const sehir = document.getElementById('filter-sehir').value.toLowerCase();
            const derece = ($('#filter-derece').val() || []).filter(Boolean);
            const turu = ($('#filter-turu').val() || []).filter(Boolean);
            const temasKurali = ($('#filter-temas-kurali').val() || []).filter(Boolean);
            const aramaPeriyoduGun = parseInt(document.getElementById('filter-arama-periyodu-gun').value, 10);
            const ziyaretPeriyoduGun = parseInt(document.getElementById('filter-ziyaret-periyodu-gun').value, 10);
            
            const tbody = document.querySelector('#musteriler-table tbody');
            const rows = tbody.querySelectorAll('tr');
            
            let visibleCount = 0;
            
            rows.forEach(row => {
                if (row.querySelector('td[colspan]')) return; // Empty row skip
                
                const rowSirket = (row.getAttribute('data-sirket') || '').toLowerCase();
                const rowSehir = (row.getAttribute('data-sehir') || '').toLowerCase();
                const rowDerece = row.getAttribute('data-derece') || '';
                const rowTuru = row.getAttribute('data-turu') || '';
                const rowTemasKurali = row.getAttribute('data-temas_kurali') || '';
                const rowAramaPeriyoduGun = parseInt(row.getAttribute('data-arama_periyodu_gun') || '0', 10);
                const rowZiyaretPeriyoduGun = parseInt(row.getAttribute('data-ziyaret_periyodu_gun') || '0', 10);
                
                let show = true;
                
                if (sirket && !rowSirket.includes(sirket)) show = false;
                if (sehir && !rowSehir.includes(sehir)) show = false;
                if (derece.length && !derece.includes(rowDerece)) show = false;
                if (turu.length && !turu.includes(rowTuru)) show = false;
                if (temasKurali.length && !temasKurali.includes(rowTemasKurali)) show = false;
                if (!Number.isNaN(aramaPeriyoduGun) && rowAramaPeriyoduGun !== aramaPeriyoduGun) show = false;
                if (!Number.isNaN(ziyaretPeriyoduGun) && rowZiyaretPeriyoduGun !== ziyaretPeriyoduGun) show = false;
                
                row.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });
            
            // Toplam sayıyı güncelle
            document.querySelector('.text-3xl.font-bold').nextElementSibling.textContent = 'Gösterilen: ' + visibleCount;
        }

        // Filtreleri temizle
        function clearFilters() {
            document.getElementById('filter-sirket').value = '';
            document.getElementById('filter-sehir').value = '';
            $('#filter-derece').val('').trigger('change');
            $('#filter-turu').val('').trigger('change');
            $('#filter-temas-kurali').val('').trigger('change');
            document.getElementById('filter-arama-periyodu-gun').value = '';
            document.getElementById('filter-ziyaret-periyodu-gun').value = '';
            
            const tbody = document.querySelector('#musteriler-table tbody');
            const rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                row.style.display = '';
            });
            
            // Toplam sayıyı geri yükle
            const totalCount = rows.length - 1; // Empty row hariç
            document.querySelector('.text-3xl.font-bold').nextElementSibling.textContent = 'Toplam: ' + totalCount;
        }

        async function saveCurrentFilter() {
            const filterName = (document.getElementById('filterName').value || '').trim();
            if (!filterName) {
                alert('Lütfen filtre adı girin!');
                return;
            }

            const formData = {};
            const form = document.getElementById('filterForm');
            form.querySelectorAll('input, select').forEach(input => {
                if (!input.name) return;
                if (input.multiple) {
                    const selected = Array.from(input.selectedOptions).map(o => o.value).filter(Boolean);
                    if (selected.length) {
                        formData[input.name] = selected;
                    }
                    return;
                }
                if (input.value) {
                    formData[input.name] = input.value;
                }
            });

            const response = await fetch('/api/saved-filters', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    name: filterName,
                    page: 'musteriler',
                    filter_data: formData
                })
            });

            if (!response.ok) {
                alert('Filtre kaydedilemedi!');
                return;
            }

            document.getElementById('filterName').value = '';
            await updateFilterButtons();
        }

        async function loadFilter(filterName) {
            const response = await fetch('/api/saved-filters?page=musteriler');
            const filters = await response.json();
            const filter = filters.find(f => f.name === filterName);
            if (!filter) return;

            const form = document.getElementById('filterForm');
            form.querySelectorAll('input, select').forEach(input => {
                if (!input.name) return;
                input.value = '';
                if ($(input).hasClass('select2-hidden-accessible')) {
                    $(input).val('').trigger('change');
                }
            });

            Object.keys(filter.filter_data || {}).forEach(key => {
                let input = form.querySelector(`[name="${key}"]`);
                if (!input && !key.endsWith('[]')) {
                    input = form.querySelector(`[name="${key}[]"]`);
                }
                if (!input) return;
                const value = filter.filter_data[key];
                if (input.multiple) {
                    $(input).val(Array.isArray(value) ? value : [value]).trigger('change');
                    return;
                }
                input.value = value;
                if ($(input).hasClass('select2-hidden-accessible')) {
                    $(input).val(value).trigger('change');
                }
            });

            applyFilters();
        }

        async function deleteFilter(filterName) {
            if (!confirm('Bu filtre silinsin mi?\n\n' + filterName)) return;

            const response = await fetch('/api/saved-filters/' + encodeURIComponent(filterName) + '?page=musteriler', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) {
                alert('Filtre silinemedi!');
                return;
            }

            await updateFilterButtons();
        }

        async function updateFilterButtons() {
            const response = await fetch('/api/saved-filters?page=musteriler');
            const filters = await response.json();
            const container = document.getElementById('savedFiltersButtons');

            if (!filters.length) {
                container.innerHTML = '<p class="text-sm text-gray-500">Henüz kayıtlı filtre yok</p>';
                return;
            }

            let html = '';
            filters.forEach(filter => {
                const safeName = String(filter.name).replace(/'/g, "\\'");
                html += `
                    <div class="inline-flex items-center gap-0.5 bg-blue-50 hover:bg-blue-100 rounded border border-blue-200 transition-colors">
                        <button type="button" onclick="loadFilter('${safeName}'); return false;" class="px-2 py-0.5 text-xs font-medium text-blue-700">
                            ${filter.name}
                        </button>
                        <button type="button" onclick="deleteFilter('${safeName}'); return false;" class="px-1.5 py-0.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-r text-xs">
                            ×
                        </button>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        const columnStorageKey = 'musteriler_column_preferences_v1';
        let selectedIds = [];

        function focusNextEditableCell(currentCell) {
            const row = currentCell.closest('tr');
            const cells = row.find('.editable-cell, .editable-select');
            const index = cells.index(currentCell);
            let next = cells.eq(index + 1);
            if (!next.length) {
                const nextRow = row.nextAll('tr').find('.editable-cell, .editable-select').first();
                if (nextRow.length) next = nextRow;
            }
            if (next && next.length) {
                setTimeout(() => next.click(), 0);
            }
        }

        // Inline editing - Text fields (Sehir, Telefon)
        $(document).on('click', '.editable-cell:not(.editing)', function() {
            const cell = $(this);
            const field = cell.data('field');
            const id = cell.data('id');
            const currentValue = cell.data('value') || '';
            
            cell.addClass('editing');
            const originalContent = cell.html();
            
            cell.html(`<input type="text" class="w-full px-2 py-1 border rounded" value="${currentValue}" />`);
            const input = cell.find('input');
            input.focus();
            
            function saveEdit() {
                const newValue = input.val();
                
                if (id === 'new') {
                    if (field !== 'sirket' && !cell.closest('tr').find('[data-field="sirket"]').data('value')) {
                        alert('Önce şirket adını girin.');
                        cell.html(originalContent);
                        cell.removeClass('editing');
                        return;
                    }

                    const payload = {
                        _token: '{{ csrf_token() }}',
                        sirket: field === 'sirket' ? newValue : (cell.closest('tr').find('[data-field="sirket"]').data('value') || ''),
                        sehir: field === 'sehir' ? newValue : (cell.closest('tr').find('[data-field="sehir"]').data('value') || ''),
                        telefon: field === 'telefon' ? newValue : (cell.closest('tr').find('[data-field="telefon"]').data('value') || ''),
                        arama_periyodu_gun: field === 'arama_periyodu_gun' ? newValue : (cell.closest('tr').find('[data-field="arama_periyodu_gun"]').data('value') || ''),
                        ziyaret_periyodu_gun: field === 'ziyaret_periyodu_gun' ? newValue : (cell.closest('tr').find('[data-field="ziyaret_periyodu_gun"]').data('value') || ''),
                        adres: field === 'adres' ? newValue : (cell.closest('tr').find('[data-field="adres"]').data('value') || ''),
                        notlar: field === 'notlar' ? newValue : (cell.closest('tr').find('[data-field="notlar"]').data('value') || '')
                    };

                    $.ajax({
                        url: '/musteriler',
                        method: 'POST',
                        data: payload,
                        success: function() {
                            location.reload();
                        },
                        error: function(xhr) {
                            alert('Kayıt oluşturulamadı! ' + (xhr.responseJSON?.message || ''));
                            cell.html(originalContent);
                            cell.removeClass('editing');
                        }
                    });
                } else {
                    $.ajax({
                        url: '/musteriler/' + id,
                        method: 'PUT',
                        data: {
                            _token: '{{ csrf_token() }}',
                            [field]: newValue
                        },
                        success: function(response) {
                            cell.data('value', newValue);
                            cell.html(newValue || '-');
                            cell.removeClass('editing');
                            
                            // Update data attribute for filtering
                            cell.closest('tr').attr('data-' + field, newValue);
                            if (field === 'derece') {
                                applySirketLinkColorByDegree(cell.closest('tr'), newValue);
                            }
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
                if (e.which === 9) { // Tab
                    e.preventDefault();
                    saveEdit();
                    focusNextEditableCell(cell);
                }
            });
        });

        // Inline editing - Select fields (Derece, Turu, Temas Kurali)
        $(document).on('click', '.editable-select:not(.editing)', function() {
            const cell = $(this);
            const field = cell.data('field');
            const id = cell.data('id');
            const currentValue = cell.data('value') || '';
            
            cell.addClass('editing');
            const originalContent = cell.html();
            
            let options = '';
            if (field === 'derece') {
                options = `
                    <option value="">Seçiniz</option>
                    <option value="1 -Sık" ${currentValue === '1 -Sık' ? 'selected' : ''}>1 - Sık</option>
                    <option value="2 - Orta" ${currentValue === '2 - Orta' ? 'selected' : ''}>2 - Orta</option>
                    <option value="3- Düşük" ${currentValue === '3- Düşük' ? 'selected' : ''}>3 - Düşük</option>
                    <option value="4 - Potansiyel" ${currentValue === '4 - Potansiyel' ? 'selected' : ''}>4 - Potansiyel</option>
                    <option value="5 - İş Ortağı" ${currentValue === '5 - İş Ortağı' ? 'selected' : ''}>5 - İş Ortağı</option>
                `;
            } else if (field === 'turu') {
                // Global listeyi kullan
                options = '<option value="">Seçiniz</option>';
                options += '<option value="__delete__">🗑 Seçili Türü Sil</option>';
                existingTuruValues.forEach(function(value) {
                    const selected = currentValue === value ? 'selected' : '';
                    options += `<option value="${value}" ${selected}>${value}</option>`;
                });
                options += '<option value="__new__">+ Yeni Tür Ekle</option>';
            } else if (field === 'temas_kurali') {
                options = `
                    <option value="">Seçiniz</option>
                    <option value="Arama Yeterli" ${currentValue === 'Arama Yeterli' ? 'selected' : ''}>Arama Yeterli</option>
                    <option value="Ziyaret Öncelikli" ${currentValue === 'Ziyaret Öncelikli' ? 'selected' : ''}>Ziyaret Öncelikli</option>
                    <option value="Her İkisi Zorunlu" ${currentValue === 'Her İkisi Zorunlu' ? 'selected' : ''}>Her İkisi Zorunlu</option>
                    <option value="Şehir Dışı (Arama Öncelikli)" ${currentValue === 'Şehir Dışı (Arama Öncelikli)' ? 'selected' : ''}>Şehir Dışı (Arama Öncelikli)</option>
                `;
            }
            
            cell.html(`<select class="inline-edit-select w-full px-2 py-1 border rounded">${options}</select>`);
            const select = cell.find('select');
            
            function getInlineSelect2Config(extra = {}) {
                return Object.assign({
                    dropdownParent: $('body'),
                    width: '100%',
                    minimumResultsForSearch: 0,
                    allowClear: false,
                    dropdownCssClass: 'select2-dropdown-inline-edit',
                    language: {
                        noResults: function() { return 'Sonuç bulunamadı'; },
                        searching: function() { return 'Aranıyor...'; }
                    }
                }, extra);
            }

            // Initialize Select2 with tags support for Türü field
            const select2Config = getInlineSelect2Config();
            
            select.select2(select2Config);
            select.select2('open');

            let isSaving = false;
            
            function saveSelect() {
                if (isSaving) return;
                isSaving = true;
                
                const newValue = select.val();

                if (field === 'turu' && newValue === '__delete__') {
                    isSaving = false;
                    if (!currentValue) {
                        alert('Silinecek bir tür seçili değil.');
                        select.val('').trigger('change.select2');
                        return;
                    }
                    if (defaultTuruValues.includes(currentValue)) {
                        alert('Varsayılan türler silinemez.');
                        select.val(currentValue);
                        return;
                    }
                    deleteTuruInline(currentValue);
                    return;
                }

                if (field === 'turu' && newValue === '__new__') {
                    isSaving = false;
                    const entered = prompt('Yeni tür adını yazın:');
                    if (!entered || !entered.trim()) {
                        select.val(currentValue || '');
                        return;
                    }

                    const newType = entered.trim();
                    if (!existingTuruValues.includes(newType)) {
                        existingTuruValues.push(newType);
                        existingTuruValues.sort();
                        if ($('#filter-turu option').filter(function() { return $(this).val() === newType; }).length === 0) {
                            $('#filter-turu').append(new Option(newType, newType, false, false));
                        }
                    }

                    if (select.find('option').filter(function() { return $(this).val() === newType; }).length === 0) {
                        select.find('option[value="__new__"]').before(new Option(newType, newType, true, true));
                    } else {
                        select.val(newType);
                    }

                    setTimeout(saveSelect, 0);
                    return;
                }
                
                if (!newValue) {
                    isSaving = false;
                    return;
                }
                
                // Destroy Select2
                select.select2('destroy');
                
                if (id === 'new') {
                    const companyName = cell.closest('tr').find('[data-field="sirket"]').data('value');
                    if (!companyName) {
                        alert('Önce şirket adını girin.');
                        cell.html(originalContent);
                        cell.removeClass('editing');
                        isSaving = false;
                        return;
                    }

                    const payload = {
                        _token: '{{ csrf_token() }}',
                        sirket: companyName,
                        sehir: cell.closest('tr').find('[data-field="sehir"]').data('value') || '',
                        telefon: cell.closest('tr').find('[data-field="telefon"]').data('value') || '',
                        arama_periyodu_gun: cell.closest('tr').find('[data-field="arama_periyodu_gun"]').data('value') || '',
                        ziyaret_periyodu_gun: cell.closest('tr').find('[data-field="ziyaret_periyodu_gun"]').data('value') || '',
                        adres: cell.closest('tr').find('[data-field="adres"]').data('value') || '',
                        notlar: cell.closest('tr').find('[data-field="notlar"]').data('value') || '',
                        [field]: newValue
                    };

                    $.ajax({
                        url: '/musteriler',
                        method: 'POST',
                        data: payload,
                        success: function() {
                            location.reload();
                        },
                        error: function(xhr) {
                            alert('Kayıt oluşturulamadı! ' + (xhr.responseJSON?.message || xhr.statusText));
                            cell.html(originalContent);
                            cell.removeClass('editing');
                            isSaving = false;
                        }
                    });
                } else {
                    $.ajax({
                        url: '/musteriler/' + id,
                        method: 'PUT',
                        data: {
                            _token: '{{ csrf_token() }}',
                            [field]: newValue
                        },
                        success: function(response) {
                            cell.data('value', newValue);
                            
                            // Eğer turu alanı için yeni bir değer eklendiyse, global listeye ekle
                            if (field === 'turu' && newValue && !existingTuruValues.includes(newValue)) {
                                existingTuruValues.push(newValue);
                                existingTuruValues.sort();
                                if ($('#filter-turu option').filter(function() { return $(this).val() === newValue; }).length === 0) {
                                    $('#filter-turu').append(new Option(newValue, newValue, false, false));
                                }
                            }
                            
                            // Rebuild the badge/display
                            if (newValue) {
                                let badgeClass = 'bg-gray-100 text-gray-800';
                                if (field === 'derece') {
                                    if (newValue === '1 -Sık') badgeClass = 'bg-red-100 text-red-800';
                                    else if (newValue === '2 - Orta') badgeClass = 'bg-yellow-100 text-yellow-800';
                                    else if (newValue === '3- Düşük') badgeClass = 'bg-green-100 text-green-800';
                                    else if (newValue === '4 - Potansiyel') badgeClass = 'bg-blue-100 text-blue-800';
                                    else if (newValue === '5 - İş Ortağı') badgeClass = 'bg-slate-200 text-slate-900';
                                } else if (field === 'turu') {
                                    // Renk paletinden al
                                    badgeClass = getColorForTuru(newValue);
                                } else if (field === 'temas_kurali') {
                                    badgeClass = 'bg-indigo-100 text-indigo-800';
                                }
                                cell.html(`<span class="px-2 py-1 text-xs rounded-full ${badgeClass}">${newValue}</span>`);
                            } else {
                                cell.html('-');
                            }
                            
                            cell.removeClass('editing');
                            
                            // Update data attribute for filtering
                            cell.closest('tr').attr('data-' + field, newValue);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', {
                                status: xhr.status,
                                statusText: xhr.statusText,
                                responseText: xhr.responseText,
                                error: error
                            });
                            alert('Kaydedilemedi! Hata: ' + (xhr.responseJSON?.message || xhr.statusText));
                            cell.html(originalContent);
                            cell.removeClass('editing');
                            isSaving = false;
                        }
                    });
                }
            }
            
            // Handle both select from list and new tag creation
            select.on('select2:select', function(e) {
                setTimeout(saveSelect, 100);
            });
            select.on('select2:close', function() {
                setTimeout(function() {
                    if (cell.hasClass('editing') && !isSaving) {
                        select.select2('destroy');
                        cell.html(originalContent);
                        cell.removeClass('editing');
                    }
                }, 200);
            });
        });

        // ==================== TOOLBAR İŞLEVLERİ ====================
        
        // Bulk delete
        window.deleteSelected = function() {
            if (selectedIds.length === 0) return;
            
            if (!confirm(selectedIds.length + ' kayıt silinecek. Emin misiniz?')) return;
            
            let deleteCount = 0;
            selectedIds.forEach(id => {
                $.ajax({
                    url: '/musteriler/' + id,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() {
                        deleteCount++;
                        if (deleteCount === selectedIds.length) {
                            location.reload();
                        }
                    }
                });
            });
        };
        
        // Duplicate selected (redirect to first selected item's page)
        window.duplicateSelected = function() {
            if (selectedIds.length === 0) return;
            window.location.href = '/musteriler/' + selectedIds[0] + '/edit?duplicate=1';
        };
        
        // Add new row
            window.addNewRow = function() {
            if ($('#musteriler-table tbody tr.new-row').length) {
                $('#musteriler-table tbody tr.new-row td.editable-cell[data-field="sirket"]').first().click();
                return;
            }

            const newRowHtml = `
                <tr class="new-row bg-yellow-50">
                    <td class="px-3 py-4 whitespace-nowrap text-center">
                        <input type="checkbox" disabled class="opacity-50">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-medium editable-cell" data-field="sirket" data-id="new" data-value=""><span class="text-gray-400">Şirket giriniz...</span></td>
                    <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="sehir" data-id="new" data-value=""><span class="text-gray-400">Şehir...</span></td>
                    <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="telefon" data-id="new" data-value=""><span class="text-gray-400">Telefon...</span></td>
                    <td class="px-6 py-4 whitespace-nowrap editable-select" data-field="derece" data-id="new" data-value="">-</td>
                    <td class="px-6 py-4 whitespace-nowrap editable-select" data-field="turu" data-id="new" data-value="">-</td>
                    <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="arama_periyodu_gun" data-id="new" data-value=""><span class="text-gray-400">Gün...</span></td>
                    <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="ziyaret_periyodu_gun" data-id="new" data-value=""><span class="text-gray-400">Gün...</span></td>
                    <td class="px-6 py-4 whitespace-nowrap editable-select" data-field="temas_kurali" data-id="new" data-value="">-</td>
                    <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="adres" data-id="new" data-value=""><span class="text-gray-400">Adres...</span></td>
                    <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="notlar" data-id="new" data-value=""><span class="text-gray-400">Not...</span></td>
                    <td class="px-6 py-4 whitespace-nowrap">-</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">-</td>
                    <td class="px-6 py-4 whitespace-nowrap">-</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">-</td>
                    <td class="px-6 py-4 whitespace-nowrap font-semibold">-</td>
                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-green-600">-</td>
                </tr>
            `;

            $('#musteriler-table tbody').prepend(newRowHtml);
            setTimeout(() => {
                $('#musteriler-table tbody tr.new-row td.editable-cell[data-field="sirket"]').first().click();
            }, 80);
        };
        
        // ==================== SÜTUN GÖRÜNÜRLÜKcontroLÜ ====================

        function getColumnIndex(columnName) {
            const columns = ['checkbox', 'sirket', 'sehir', 'telefon', 'derece', 'turu', 'arama_periyodu_gun', 'ziyaret_periyodu_gun', 'temas_kurali', 'adres', 'notlar', 'quick_actions', 'en_son_ziyaret', 'son_baglanti_turu', 'ziyaret_gun', 'ziyaret_adeti', 'toplam_teklif', 'kazanildi_toplami'];
            return columns.indexOf(columnName);
        }

        function openQuickNoteModal(visitId) {
            quickNoteVisitId = visitId;
            $('#quick-note-text').val('');
            $('#quick-note-modal').css('display', 'flex');
            setTimeout(() => $('#quick-note-text').trigger('focus'), 10);
        }

        function closeQuickNoteModal() {
            quickNoteVisitId = null;
            $('#quick-note-modal').hide();
            $('#quick-note-text').val('');
        }

        function parseErrorMessage(xhr) {
            const json = xhr && xhr.responseJSON ? xhr.responseJSON : null;
            if (json && json.message) return json.message;
            if (xhr && xhr.statusText) return xhr.statusText;
            return 'Bilinmeyen hata';
        }

        $(document).on('click', '.quick-contact-btn', function () {
            const $btn = $(this);
            const musteriId = String($btn.data('musteri-id') || '').trim();
            const musteriName = String($btn.data('musteri-name') || '').trim();
            const contactType = String($btn.data('contact-type') || '').trim();

            if (!musteriId || !contactType) return;

            const lockKey = musteriId + '::' + contactType;
            const nowMs = Date.now();
            if (quickContactLock[lockKey] && nowMs - quickContactLock[lockKey] < 3000) {
                return;
            }
            quickContactLock[lockKey] = nowMs;

            const prevText = $btn.text();
            $btn.prop('disabled', true).text('...');

            $.ajax({
                url: '/musteriler/' + musteriId + '/quick-contact',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    contact_type: contactType
                },
                success: function (res) {
                    const id = res?.data?.id;
                    alert((musteriName || 'Müşteri') + ' için ' + (contactType === 'Telefon' ? 'arama' : 'ziyaret') + ' kaydı oluşturuldu.');
                    if (id) {
                        openQuickNoteModal(id);
                    }
                },
                error: function (xhr) {
                    alert('Hızlı kayıt oluşturulamadı! ' + parseErrorMessage(xhr));
                },
                complete: function () {
                    setTimeout(function () {
                        delete quickContactLock[lockKey];
                    }, 3200);
                    $btn.prop('disabled', false).text(prevText);
                }
            });
        });

        $('#quick-note-close, #quick-note-later').on('click', function () {
            closeQuickNoteModal();
        });

        $('#quick-note-save').on('click', function () {
            const text = String($('#quick-note-text').val() || '').trim();
            if (!quickNoteVisitId) {
                closeQuickNoteModal();
                return;
            }
            if (!text) {
                alert('Not boş olamaz. Boş bırakacaksan Sonra seç.');
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true).text('Kaydediliyor...');

            $.ajax({
                url: '/ziyaretler/' + quickNoteVisitId + '/quick-note',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ziyaret_notlari: text
                },
                success: function () {
                    alert('Not kaydedildi.');
                    closeQuickNoteModal();
                },
                error: function (xhr) {
                    alert('Not kaydedilemedi! ' + parseErrorMessage(xhr));
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Notu Kaydet');
                }
            });
        });

        const toolbar = window.CrmToolbar.init({
            storageKey: columnStorageKey,
            onColumnToggle: function(column, isVisible) {
                const columnIndex = getColumnIndex(column);
                if (columnIndex !== -1) {
                    $(`#musteriler-table thead tr th:eq(${columnIndex})`).toggle(isVisible);
                    $(`#musteriler-table tbody tr`).each(function() {
                        $(this).find(`td:eq(${columnIndex})`).toggle(isVisible);
                    });
                }
                setTimeout(() => {
                    document.getElementById('scroll-content-top').style.width = document.getElementById('musteriler-table').offsetWidth + 'px';
                }, 100);
            },
            onSelectionChange: function(ids) {
                selectedIds = ids;
            }
        });
    </script>
    
</body>
</html>
