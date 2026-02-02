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
                                    <input type="checkbox" class="column-toggle" data-column="ad_soyad" checked> Ad Soyad
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="firma" checked> Firma
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="telefon" checked> Telefon
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="email"> Email
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="bolum" checked> Bölüm
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="gorev"> Görev
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="url"> URL
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
                <table id="kisiler-table" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-center">
                                <input type="checkbox" id="select-all" class="cursor-pointer">
                            </th>
                            <th class="sortable px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-32" data-column="ad_soyad">Ad Soyad <span class="sort-icon"></span></th>
                            <th class="sortable px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-40" data-column="firma">Firma <span class="sort-icon"></span></th>
                            <th class="sortable px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-32" data-column="telefon_numarasi">Telefon <span class="sort-icon"></span></th>
                            <th class="sortable px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-32" data-column="email_adresi">Email <span class="sort-icon"></span></th>
                            <th class="sortable px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-24" data-column="bolum">Bölüm <span class="sort-icon"></span></th>
                            <th class="sortable px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-24" data-column="gorev">Görev <span class="sort-icon"></span></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-20">URL</th>
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
                                data-telefon_numarasi="{{ $kisi->telefon_numarasi ?? '' }}"
                                data-email_adresi="{{ $kisi->email_adresi ?? '' }}"
                                data-bolum="{{ $kisi->bolum ?? '' }}" 
                                data-gorev="{{ $kisi->gorev ?? '' }}">
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <input type="checkbox" class="row-checkbox cursor-pointer" data-id="{{ $kisi->id }}">
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap font-medium text-sm editable-cell" data-field="ad_soyad" data-id="{{ $kisi->id }}" data-value="{{ $kisi->ad_soyad }}">{{ $kisi->ad_soyad }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm editable-select" data-field="musteri_id" data-id="{{ $kisi->id }}" data-value="{{ $kisi->musteri_id ?? '' }}">
                                    @if($kisi->musteri)
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ $kisi->musteri->sirket }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm editable-cell" data-field="telefon_numarasi" data-id="{{ $kisi->id }}" data-value="{{ $kisi->telefon_numarasi }}">{{ $kisi->telefon_numarasi ?? '-' }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm editable-cell" data-field="email_adresi" data-id="{{ $kisi->id }}" data-value="{{ $kisi->email_adresi }}">{{ $kisi->email_adresi ?? '-' }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm editable-cell" data-field="bolum" data-id="{{ $kisi->id }}" data-value="{{ $kisi->bolum }}">{{ $kisi->bolum ?? '-' }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm editable-cell" data-field="gorev" data-id="{{ $kisi->id }}" data-value="{{ $kisi->gorev }}">{{ $kisi->gorev ?? '-' }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm editable-cell" data-field="url" data-id="{{ $kisi->id }}" data-value="{{ $kisi->url }}">
                                    @if($kisi->url)
                                        <a href="{{ $kisi->url }}" target="_blank" class="text-blue-600 hover:underline">
                                            🔗 Link
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
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
                header.addEventListener('click', function(e) {
                    // Eğer checkbox'a tıkladıysak sıralama yapma
                    if (e.target.type === 'checkbox') return;
                    
                    const column = this.getAttribute('data-column');
                    if (!column) return;
                    
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
                    const sortIcon = this.querySelector('.sort-icon');
                    if (sortIcon) sortIcon.textContent = isAsc ? ' ▲' : ' ▼';
                    
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

        // ==================== TOOLBAR İŞLEVLERİ ====================
        
        // Checkbox selection management
        let selectedIds = [];
        
        $('#select-all').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.row-checkbox').prop('checked', isChecked);
            updateSelection();
        });
        
        $(document).on('change', '.row-checkbox', function() {
            updateSelection();
        });
        
        function updateSelection() {
            selectedIds = $('.row-checkbox:checked').map(function() {
                return $(this).data('id');
            }).get();
            
            $('#btn-duplicate, #btn-delete').prop('disabled', selectedIds.length === 0);
            
            if (selectedIds.length > 0) {
                $('#selection-count').text(selectedIds.length + ' kayıt seçili');
            } else {
                $('#selection-count').text('');
                $('#select-all').prop('checked', false);
            }
        }
        
        // Bulk delete
        window.deleteSelected = function() {
            if (selectedIds.length === 0) return;
            
            if (!confirm(selectedIds.length + ' kayıt silinecek. Emin misiniz?')) return;
            
            let deleteCount = 0;
            selectedIds.forEach(id => {
                $.ajax({
                    url: '/kisiler/' + id,
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
        
        // Duplicate selected
        window.duplicateSelected = function() {
            if (selectedIds.length === 0) return;
            window.location.href = '/kisiler/' + selectedIds[0] + '/edit?duplicate=1';
        };
        
        // Add new row
        window.addNewRow = function() {
            const form = document.getElementById('kisi-ekle-form');
            const icon = document.getElementById('form-toggle-icon');
            
            if (form && form.style.display === 'none') {
                form.style.display = 'block';
                if (icon) icon.style.transform = 'rotate(180deg)';
            }
            
            setTimeout(() => {
                const input = document.querySelector('#kisi-ekle-form input[name="ad_soyad"]');
                if (input) input.focus();
            }, 100);
        };
        
        // ==================== SÜTUN GÖRÜNÜRLÜKcontrôlÜ ====================
        
        $('#column-toggle-btn').on('click', function(e) {
            e.stopPropagation();
            $('#column-menu').toggleClass('hidden');
            $('#column-arrow').text($('#column-menu').hasClass('hidden') ? '▼' : '▲');
        });
        
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#column-toggle-btn, #column-menu').length) {
                $('#column-menu').addClass('hidden');
                $('#column-arrow').text('▼');
            }
        });
        
        $('.column-toggle').on('change', function() {
            const column = $(this).data('column');
            const isVisible = $(this).is(':checked');
            const columnIndex = getColumnIndex(column);
            
            if (columnIndex !== -1) {
                $(`#kisiler-table thead tr th:eq(${columnIndex})`).toggle(isVisible);
                $(`#kisiler-table tbody tr`).each(function() {
                    $(this).find(`td:eq(${columnIndex})`).toggle(isVisible);
                });
            }
            
            setTimeout(() => {
                document.getElementById('scroll-content-top').style.width = document.getElementById('kisiler-table').offsetWidth + 'px';
            }, 100);
        });
        
        function getColumnIndex(columnName) {
            const columns = ['checkbox', 'ad_soyad', 'firma', 'telefon', 'email', 'bolum', 'gorev', 'url'];
            return columns.indexOf(columnName);
        }
        
        // ==================== INLINE EDITING ====================
        
        // Text fields
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
                    url: '/kisiler/' + id,
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        [field]: newValue
                    },
                    success: function(response) {
                        cell.data('value', newValue);
                        
                        // For URL field, rebuild the link
                        if (field === 'url' && newValue) {
                            cell.html(`<a href="${newValue}" target="_blank" class="text-blue-600 hover:underline">🔗 Link</a>`);
                        } else {
                            cell.html(newValue || '-');
                        }
                        
                        cell.removeClass('editing');
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
                if (e.which === 13) saveEdit();
            });
            input.on('keydown', function(e) {
                if (e.which === 27) {
                    cell.html(originalContent);
                    cell.removeClass('editing');
                }
            });
        });
        
        // Firma select field with Select2
        $(document).on('click', '.editable-select:not(.editing)', function() {
            const cell = $(this);
            const field = cell.data('field');
            const id = cell.data('id');
            const currentValue = cell.data('value') || '';
            
            cell.addClass('editing');
            const originalContent = cell.html();
            
            let options = '<option value="">Seçiniz</option>';
            @foreach(\App\Models\Musteri::orderBy('sirket')->get() as $m)
                options += `<option value="{{ $m->id }}" ${currentValue == "{{ $m->id }}" ? 'selected' : ''}>{{ $m->sirket }}</option>`;
            @endforeach
            
            cell.html(`<select class="inline-edit-select w-full px-2 py-1 border rounded">${options}</select>`);
            const select = cell.find('select');
            
            // Initialize Select2
            select.select2({
                dropdownParent: $('body'),
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownCssClass: 'select2-dropdown-inline-edit'
            });
            
            select.select2('open');
            
            function saveSelect() {
                const newValue = select.val();
                const newText = select.find('option:selected').text();
                
                // Destroy Select2
                select.select2('destroy');
                
                $.ajax({
                    url: '/kisiler/' + id,
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        [field]: newValue
                    },
                    success: function(response) {
                        cell.data('value', newValue);
                        
                        if (newValue) {
                            cell.html(`<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">${newText}</span>`);
                        } else {
                            cell.html('-');
                        }
                        
                        cell.removeClass('editing');
                        cell.closest('tr').attr('data-' + field, newValue);
                    },
                    error: function() {
                        alert('Kaydedilemedi!');
                        cell.html(originalContent);
                        cell.removeClass('editing');
                    }
                });
            }
            
            select.on('select2:select', saveSelect);
            select.on('select2:close', function() {
                if (cell.hasClass('editing')) {
                    select.select2('destroy');
                    cell.html(originalContent);
                    cell.removeClass('editing');
                }
            });
            });
        });
    </script>
</body>
</html>