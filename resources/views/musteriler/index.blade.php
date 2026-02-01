<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    </style>
</head>
<body class="bg-gray-100">
    @include('layouts.nav')
    
    <div class="container mx-auto px-4 py-8">
        @php
            $toplamMusteri = \App\Models\Musteri::count();
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

        <!-- Form -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 flex justify-between items-center cursor-pointer" onclick="toggleForm()">
                <h2 class="text-xl font-bold">Yeni Müşteri Ekle</h2>
                <span id="form-toggle-icon" class="text-2xl transform transition-transform">▼</span>
            </div>
            <div id="musteri-ekle-form" style="display: none;">
                <form method="POST" action="/musteriler" class="space-y-4 px-6 pb-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Şirket Adı *</label>
                        <input type="text" name="sirket" required class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Şehir</label>
                        <input type="text" name="sehir" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Telefon</label>
                        <input type="text" name="telefon" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Derece</label>
                        <select name="derece" id="derece-select" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="1 -Sık">1 - Sık</option>
                            <option value="2 - Orta">2 - Orta</option>
                            <option value="3- Düşük">3 - Düşük</option>
                            <option value="4 - Hiç">4 - Hiç</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Türü</label>
                        <select name="turu" id="turu-select" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
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
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Adres</label>
                    <textarea name="adres" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Notlar</label>
                    <textarea name="notlar" rows="3" class="w-full border rounded px-3 py-2"></textarea>
                </div>
                
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    Müşteri Ekle
                </button>
            </form>
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
                            <select name="derece" id="filter-derece" class="w-full border rounded px-3 py-2 select2-filter">
                                <option value="">Tümü</option>
                                <option value="1 -Sık">1 - Sık</option>
                                <option value="2 - Orta">2 - Orta</option>
                                <option value="3- Düşük">3 - Düşük</option>
                                <option value="4 - Hiç">4 - Hiç</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Türü</label>
                            <select name="turu" id="filter-turu" class="w-full border rounded px-3 py-2 select2-filter">
                                <option value="">Tümü</option>
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
            <!-- Üst scroll bar -->
            <div id="scroll-top" class="scroll-sync" style="overflow-x: auto; height: 20px;">
                <div id="scroll-content-top" style="height: 1px;"></div>
            </div>
            
            <div id="scroll-bottom" class="scroll-sync overflow-x-auto">
                <table id="musteriler-table" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="sirket">Şirket <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="sehir">Şehir <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="telefon">Telefon <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="derece">Derece <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="turu">Türü <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="en_son_ziyaret">Son Ziyaret <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="ziyaret_gun">Ziyaret Gün <span class="sort-icon"></span></th>
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
                                data-derece="{{ $musteri->derece ?? '' }}" 
                                data-turu="{{ $musteri->turu ?? '' }}" 
                                data-en_son_ziyaret="{{ $musteri->en_son_ziyaret ?? '' }}" 
                                data-ziyaret_gun="{{ (int)($musteri->ziyaret_gun ?? 0) }}" 
                                data-ziyaret_adeti="{{ $musteri->ziyaret_adeti }}" 
                                data-toplam_teklif="{{ $musteri->toplam_teklif }}" 
                                data-kazanildi_toplami="{{ $musteri->kazanildi_toplami }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="/musteriler/{{ $musteri->id }}/edit" class="text-blue-600 hover:text-blue-800 mr-3">
                                        ✏️ Düzenle
                                    </a>
                                    <form action="/musteriler/{{ $musteri->id }}" method="POST" class="inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            🗑️ Sil
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium">
                                    <a href="/musteriler/{{ $musteri->id }}" class="text-blue-600 hover:text-blue-800 hover:underline">
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
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ $musteri->turu }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $musteri->en_son_ziyaret ? $musteri->en_son_ziyaret->format('d.m.Y') : '-' }}
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
                                <td colspan="11" class="px-6 py-4 text-center text-gray-500">
                                    Henüz müşteri kaydı yok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Select2 başlat
            $('#derece-select, #turu-select, .select2-filter').select2({
                placeholder: 'Seçiniz...',
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
                        if (['ziyaret_gun', 'ziyaret_adeti', 'toplam_teklif', 'kazanildi_toplami'].includes(column)) {
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

        // Form toggle fonksiyonu
        function toggleForm() {
            const form = document.getElementById('musteri-ekle-form');
            const icon = document.getElementById('form-toggle-icon');
            
            if (form.style.display === 'none') {
                form.style.display = 'block';
                icon.style.transform = 'rotate(180deg)';
            } else {
                form.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
            }
        }

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
            const derece = document.getElementById('filter-derece').value;
            const turu = document.getElementById('filter-turu').value;
            
            const tbody = document.querySelector('#musteriler-table tbody');
            const rows = tbody.querySelectorAll('tr');
            
            let visibleCount = 0;
            
            rows.forEach(row => {
                if (row.querySelector('td[colspan]')) return; // Empty row skip
                
                const rowSirket = (row.getAttribute('data-sirket') || '').toLowerCase();
                const rowSehir = (row.getAttribute('data-sehir') || '').toLowerCase();
                const rowDerece = row.getAttribute('data-derece') || '';
                const rowTuru = row.getAttribute('data-turu') || '';
                
                let show = true;
                
                if (sirket && !rowSirket.includes(sirket)) show = false;
                if (sehir && !rowSehir.includes(sehir)) show = false;
                if (derece && rowDerece !== derece) show = false;
                if (turu && rowTuru !== turu) show = false;
                
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
            
            const tbody = document.querySelector('#musteriler-table tbody');
            const rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                row.style.display = '';
            });
            
            // Toplam sayıyı geri yükle
            const totalCount = rows.length - 1; // Empty row hariç
            document.querySelector('.text-3xl.font-bold').nextElementSibling.textContent = 'Toplam: ' + totalCount;
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
                    },
                    error: function() {
                        alert('Kaydedilemedi!');
                        cell.html(originalContent);
                        cell.removeClass('editing');
                    }
                });
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

        // Inline editing - Select fields (Derece, Turu)
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
                    <option value="4 - Hiç" ${currentValue === '4 - Hiç' ? 'selected' : ''}>4 - Hiç</option>
                `;
            } else if (field === 'turu') {
                options = `
                    <option value="">Seçiniz</option>
                    <option value="Netcom" ${currentValue === 'Netcom' ? 'selected' : ''}>Netcom</option>
                    <option value="Bayi" ${currentValue === 'Bayi' ? 'selected' : ''}>Bayi</option>
                    <option value="Resmi Kurum" ${currentValue === 'Resmi Kurum' ? 'selected' : ''}>Resmi Kurum</option>
                    <option value="Üniversite" ${currentValue === 'Üniversite' ? 'selected' : ''}>Üniversite</option>
                    <option value="Belediye" ${currentValue === 'Belediye' ? 'selected' : ''}>Belediye</option>
                    <option value="Hastane" ${currentValue === 'Hastane' ? 'selected' : ''}>Hastane</option>
                    <option value="Özel Sektör" ${currentValue === 'Özel Sektör' ? 'selected' : ''}>Özel Sektör</option>
                    <option value="Diğer" ${currentValue === 'Diğer' ? 'selected' : ''}>Diğer</option>
                `;
            }
            
            cell.html(`<select class="w-full px-2 py-1 border rounded">${options}</select>`);
            const select = cell.find('select');
            select.focus();
            
            function saveSelect() {
                const newValue = select.val();
                
                $.ajax({
                    url: '/musteriler/' + id,
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        [field]: newValue
                    },
                    success: function(response) {
                        cell.data('value', newValue);
                        
                        // Rebuild the badge/display
                        if (newValue) {
                            let badgeClass = 'bg-gray-100 text-gray-800';
                            if (field === 'derece') {
                                if (newValue === '1 -Sık') badgeClass = 'bg-red-100 text-red-800';
                                else if (newValue === '2 - Orta') badgeClass = 'bg-yellow-100 text-yellow-800';
                                else if (newValue === '3- Düşük') badgeClass = 'bg-green-100 text-green-800';
                            } else if (field === 'turu') {
                                badgeClass = 'bg-blue-100 text-blue-800';
                            }
                            cell.html(`<span class="px-2 py-1 text-xs rounded-full ${badgeClass}">${newValue}</span>`);
                        } else {
                            cell.html('-');
                        }
                        
                        cell.removeClass('editing');
                        
                        // Update data attribute for filtering
                        cell.closest('tr').attr('data-' + field, newValue);
                    },
                    error: function() {
                        alert('Kaydedilemedi!');
                        cell.html(originalContent);
                        cell.removeClass('editing');
                    }
                });
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
    </script>
</body>
</html>