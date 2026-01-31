<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kişiler - CRM</title>
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
    </style>
</head>
<body class="bg-gray-100">
    @include('layouts.nav')
    
    <div class="container mx-auto px-4 py-8">
        @php
            $toplamKisi = \App\Models\Kisi::count();
        @endphp
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Kişiler</h1>
            <span class="text-lg font-semibold text-gray-600">Toplam: {{ $toplamKisi }}</span>
        </div>
        
        @if(session('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        <!-- Form -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 flex justify-between items-center cursor-pointer" onclick="toggleForm()">
                <h2 class="text-xl font-bold">Yeni Kişi Ekle</h2>
                <span id="form-toggle-icon" class="text-2xl transform transition-transform">▼</span>
            </div>
            <div id="kisi-ekle-form" style="display: none;">
                <form method="POST" action="/kisiler" class="space-y-4 px-6 pb-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Ad Soyad *</label>
                        <input type="text" name="ad_soyad" required class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Firma</label>
                        <select name="musteri_id" id="firma-select" class="w-full border rounded px-3 py-2">
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
                        <label class="block text-sm font-medium mb-1">Telefon</label>
                        <input type="text" name="telefon_numarasi" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="email_adresi" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Bölüm</label>
                        <input type="text" name="bolum" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Görev</label>
                        <input type="text" name="gorev" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">URL</label>
                        <input type="url" name="url" class="w-full border rounded px-3 py-2" placeholder="https://...">
                    </div>
                </div>
                
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    Kişi Ekle
                </button>
            </form>
            </div>
        </div>

        <!-- Filtreler -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 flex justify-between items-center cursor-pointer" onclick="toggleFilters()">
                <h2 class="text-xl font-bold">Filtreler</h2>
                <span id="filter-toggle-icon" class="text-2xl transform transition-transform">▼</span>
            </div>
            <div id="filters-form" style="display: none;">
                <div class="space-y-4 px-6 pb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Ad / Soyadı</label>
                        <input type="text" id="filter-ad_soyad" class="w-full border rounded px-3 py-2" placeholder="Ara...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Firma</label>
                        <select id="filter-firma_id" class="w-full border rounded px-3 py-2">
                            <option value="">Tümü</option>
                            @foreach(\App\Models\Musteri::orderBy('sirket')->get() as $m)
                                <option value="{{ $m->id }}">
                                    {{ $m->sirket }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Bölüm</label>
                        <input type="text" id="filter-bolum" class="w-full border rounded px-3 py-2" placeholder="Ara...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Görev</label>
                        <input type="text" id="filter-gorev" class="w-full border rounded px-3 py-2" placeholder="Ara...">
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <button type="button" onclick="applyFilters()" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                        Filtrele
                    </button>
                    <button type="button" onclick="clearFilters()" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                        Temizle
                    </button>
                </div>
            </div>
            </div>
        </div>

        <!-- Liste -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Üst scroll bar -->
            <div id="scroll-top" class="scroll-sync" style="overflow-x: auto; height: 20px;">
                <div id="scroll-content-top" style="height: 1px;"></div>
            </div>
            
            <div id="scroll-bottom" class="scroll-sync overflow-x-auto">
                <table id="kisiler-table" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="sortable px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-32" data-column="ad_soyad">Ad Soyad <span class="sort-icon"></span></th>
                            <th class="sortable px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-40" data-column="firma">Firma <span class="sort-icon"></span></th>
                            <th class="sortable px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-32" data-column="telefon_numarasi">Telefon <span class="sort-icon"></span></th>
                            <th class="sortable px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-24" data-column="bolum">Bölüm <span class="sort-icon"></span></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-20">URL</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-24">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            // Filtreleme
                            $kisiler = \App\Models\Kisi::with('musteri')->latest();
                            
                            if (request('ad_soyad')) {
                                $kisiler->where('ad_soyad', 'like', '%' . request('ad_soyad') . '%');
                            }
                            if (request('firma_id')) {
                                $kisiler->where('musteri_id', request('firma_id'));
                            }
                            if (request('bolum')) {
                                $kisiler->where('bolum', 'like', '%' . request('bolum') . '%');
                            }
                            if (request('gorev')) {
                                $kisiler->where('gorev', 'like', '%' . request('gorev') . '%');
                            }
                            
                            $kisiler = $kisiler->get();
                        @endphp
                        
                        @forelse($kisiler as $kisi)
                            <tr data-ad_soyad="{{ $kisi->ad_soyad }}" 
                                data-firma_id="{{ $kisi->musteri_id ?? '' }}"
                                data-bolum="{{ $kisi->bolum ?? '' }}" 
                                data-gorev="{{ $kisi->gorev ?? '' }}">
                                <td class="px-4 py-4 whitespace-nowrap font-medium text-sm">{{ $kisi->ad_soyad }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">
                                    @if($kisi->musteri)
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ $kisi->musteri->sirket }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $kisi->telefon_numarasi ?? '-' }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $kisi->bolum ?? '-' }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">
                                    @if($kisi->url)
                                        <a href="{{ $kisi->url }}" target="_blank" class="text-blue-600 hover:underline">
                                            🔗 Link
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">
                                    <a href="/kisiler/{{ $kisi->id }}/edit" class="text-blue-600 hover:text-blue-800 mr-3">
                                        ✏️ Düzenle
                                    </a>
                                    <form action="/kisiler/{{ $kisi->id }}" method="POST" class="inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            🗑️ Sil
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    Henüz kişi kaydı yok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Form ve filtre toggle fonksiyonları
        function toggleForm() {
            const form = document.getElementById('kisi-ekle-form');
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
        
        function applyFilters() {
            const adSoyad = document.getElementById('filter-ad_soyad').value.toLowerCase();
            const firmaId = document.getElementById('filter-firma_id').value;
            const bolum = document.getElementById('filter-bolum').value.toLowerCase();
            const gorev = document.getElementById('filter-gorev').value.toLowerCase();
            
            const rows = document.querySelectorAll('#kisiler-table tbody tr');
            
            rows.forEach(row => {
                const rowAdSoyad = row.getAttribute('data-ad_soyad').toLowerCase();
                const rowFirmaId = row.getAttribute('data-firma_id') || '';
                const rowBolum = row.getAttribute('data-bolum').toLowerCase();
                const rowGorev = row.getAttribute('data-gorev').toLowerCase();
                
                const adSoyadMatch = rowAdSoyad.includes(adSoyad);
                const firmaMatch = !firmaId || rowFirmaId === firmaId;
                const bolumMatch = rowBolum.includes(bolum);
                const gorevMatch = rowGorev.includes(gorev);
                
                if (adSoyadMatch && firmaMatch && bolumMatch && gorevMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        function clearFilters() {
            document.getElementById('filter-ad_soyad').value = '';
            document.getElementById('filter-firma_id').value = '';
            document.getElementById('filter-bolum').value = '';
            document.getElementById('filter-gorev').value = '';
            applyFilters();
        }

        $(document).ready(function() {
            // Select2 başlat
            $('#firma-select, #filter-firma-select').select2({
                placeholder: 'Firma ara...',
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

            // Scroll senkronizasyonu
            const scrollTop = document.getElementById('scroll-top');
            const scrollBottom = document.getElementById('scroll-bottom');
            const table = document.getElementById('kisiler-table');
            
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
                    const tbody = document.querySelector('#kisiler-table tbody');
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
    </script>
</body>
</html>