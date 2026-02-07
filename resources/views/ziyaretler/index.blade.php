<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ziyaretler - CRM</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        .editable-cell, .editable-select, .editable-date {
            cursor: pointer;
        }
        .editable-cell:hover, .editable-select:hover, .editable-date:hover {
            background-color: #fef3c7 !important;
        }
        .editing {
            padding: 0 !important;
        }
        .toolbar-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .note-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }
        .note-modal.open {
            display: flex;
        }
    </style>
</head>
<body class="bg-gray-100">
    @include('layouts.nav')
    
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Ziyaret Takip</h1>
        
        @if(session('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        <!-- Filtreler -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 flex justify-between items-center cursor-pointer" onclick="toggleFilters()">
                <h2 class="text-xl font-bold">Filtreler</h2>
                <span id="filter-toggle-icon" class="text-2xl transform transition-transform">▼</span>
            </div>
            <div id="filters-form" style="display: none;">
                <form method="GET" action="/ziyaretler" class="space-y-4 px-6 pb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Ziyaret İsmi</label>
                            <input type="text" name="ziyaret_ismi" value="{{ request('ziyaret_ismi') }}" placeholder="Ziyaret ismini ara..." class="w-full border rounded px-3 py-2">
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
                            <label class="block text-sm font-medium mb-1">Tür</label>
                            <select name="tur" class="w-full border rounded px-3 py-2">
                                <option value="">Tümü</option>
                                <option value="Ziyaret" {{ request('tur') == 'Ziyaret' ? 'selected' : '' }}>Ziyaret</option>
                                <option value="Telefon" {{ request('tur') == 'Telefon' ? 'selected' : '' }}>Telefon</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Durumu</label>
                            <select name="durumu" class="w-full border rounded px-3 py-2">
                                <option value="">Tümü</option>
                                <option value="Beklemede" {{ request('durumu') == 'Beklemede' ? 'selected' : '' }}>Beklemede</option>
                                <option value="Planlandı" {{ request('durumu') == 'Planlandı' ? 'selected' : '' }}>Planlandı</option>
                                <option value="Tamamlandı" {{ request('durumu') == 'Tamamlandı' ? 'selected' : '' }}>Tamamlandı</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Başlangıç Tarihi</label>
                            <input type="date" name="tarih_baslangic" value="{{ request('tarih_baslangic') }}" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Bitiş Tarihi</label>
                            <input type="date" name="tarih_bitis" value="{{ request('tarih_bitis') }}" class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                            Filtrele
                        </button>
                        <a href="/ziyaretler" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                            Temizle
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Toolbar -->
            <div class="px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button onclick="addNewRow()" class="toolbar-btn bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex items-center gap-2 transition">
                            ➕ Ekle
                        </button>
                        <button onclick="duplicateSelected()" id="btn-duplicate" disabled class="toolbar-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded flex items-center gap-2 transition">
                            📋 Kopyala
                        </button>
                        <button onclick="deleteSelected()" id="btn-delete" disabled class="toolbar-btn bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded flex items-center gap-2 transition">
                            🗑️ Sil
                        </button>
                        <span id="selection-count" class="text-sm text-gray-600"></span>
                    </div>
                    <div class="relative inline-block">
                        <button id="column-toggle-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded flex items-center gap-2">
                            <span>📊 Sütunlar</span>
                            <span id="column-arrow">▼</span>
                        </button>
                        <div id="column-menu" class="hidden absolute right-0 mt-2 w-64 bg-white border rounded-lg shadow-lg z-50 p-3 max-h-96 overflow-y-auto">
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="ziyaret_ismi" checked> Ziyaret
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="musteri" checked> Müşteri
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="ziyaret_tarihi" checked> Ziyaret Tarihi
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="tur" checked> Tür
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="durumu" checked> Durum
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="ziyaret_notlari" checked> Notlar
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
                <table id="ziyaretler-table" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-center">
                                <input type="checkbox" id="select-all" class="cursor-pointer">
                            </th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase col-ziyaret_ismi" data-column="ziyaret_ismi">Ziyaret <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase col-musteri" data-column="musteri">Müşteri <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase col-ziyaret_tarihi" data-column="ziyaret_tarihi">Ziyaret Tarihi <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase col-tur" data-column="tur">Tür <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase col-durumu" data-column="durumu">Durum <span class="sort-icon"></span></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase col-ziyaret_notlari">Notlar</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $query = \App\Models\Ziyaret::with('musteri');
                            
                            if(request('ziyaret_ismi')) {
                                $query->where('ziyaret_ismi', 'like', '%' . request('ziyaret_ismi') . '%');
                            }
                            if(request('musteri_id')) {
                                $query->where('musteri_id', request('musteri_id'));
                            }
                            if(request('tur')) {
                                $query->where('tur', request('tur'));
                            }
                            if(request('durumu')) {
                                $query->where('durumu', request('durumu'));
                            }
                            if(request('tarih_baslangic')) {
                                $query->whereDate('ziyaret_tarihi', '>=', request('tarih_baslangic'));
                            }
                            if(request('tarih_bitis')) {
                                $query->whereDate('ziyaret_tarihi', '<=', request('tarih_bitis'));
                            }
                            
                            $ziyaretler = $query->latest('ziyaret_tarihi')->get();
                        @endphp
                        
                        @forelse($ziyaretler as $ziyaret)
                            <tr data-row="1"
                                data-id="{{ $ziyaret->id }}"
                                data-ziyaret-ismi="{{ $ziyaret->ziyaret_ismi }}"
                                data-ziyaret_ismi="{{ $ziyaret->ziyaret_ismi }}"
                                data-musteri="{{ $ziyaret->musteri ? $ziyaret->musteri->sirket : '' }}" 
                                data-musteri-id="{{ $ziyaret->musteri_id ?? '' }}"
                                data-musteri_id="{{ $ziyaret->musteri_id ?? '' }}"
                                data-ziyaret-tarihi="{{ $ziyaret->ziyaret_tarihi }}" 
                                data-ziyaret_tarihi="{{ $ziyaret->ziyaret_tarihi }}"
                                data-arama-tarihi="{{ $ziyaret->arama_tarihi }}"
                                data-arama_tarihi="{{ $ziyaret->arama_tarihi }}"
                                data-tur="{{ $ziyaret->tur ?? '' }}" 
                                data-durumu="{{ $ziyaret->durumu ?? '' }}"
                                data-ziyaret-notlari="{{ $ziyaret->ziyaret_notlari ?? '' }}"
                                data-ziyaret_notlari="{{ $ziyaret->ziyaret_notlari ?? '' }}">
                                <td class="px-3 py-4 text-center">
                                    <input type="checkbox" class="row-checkbox cursor-pointer" data-id="{{ $ziyaret->id }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium editable-cell col-ziyaret_ismi" data-field="ziyaret_ismi" data-id="{{ $ziyaret->id }}" data-value="{{ $ziyaret->ziyaret_ismi }}">
                                    {{ $ziyaret->ziyaret_ismi }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap editable-select col-musteri" data-field="musteri_id" data-id="{{ $ziyaret->id }}" data-value="{{ $ziyaret->musteri_id ?? '' }}">
                                    @if($ziyaret->musteri)
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ $ziyaret->musteri->sirket }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap editable-date col-ziyaret_tarihi" data-field="tarih" data-id="{{ $ziyaret->id }}" data-value="{{ $ziyaret->tur == 'Telefon' ? $ziyaret->arama_tarihi : $ziyaret->ziyaret_tarihi }}">
                                    @if($ziyaret->tur == 'Telefon' && $ziyaret->arama_tarihi)
                                        {{ \Carbon\Carbon::parse($ziyaret->arama_tarihi)->format('d.m.Y') }}
                                    @elseif($ziyaret->ziyaret_tarihi)
                                        {{ $ziyaret->ziyaret_tarihi->format('d.m.Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap editable-select col-tur" data-field="tur" data-id="{{ $ziyaret->id }}" data-value="{{ $ziyaret->tur ?? '' }}">
                                    @if($ziyaret->tur)
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $ziyaret->tur == 'Ziyaret' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $ziyaret->tur }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap editable-select col-durumu" data-field="durumu" data-id="{{ $ziyaret->id }}" data-value="{{ $ziyaret->durumu ?? '' }}">
                                    @if($ziyaret->durumu)
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($ziyaret->durumu == 'Beklemede') bg-yellow-100 text-yellow-800
                                            @elseif($ziyaret->durumu == 'Planlandı') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ $ziyaret->durumu }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm editable-cell col-ziyaret_notlari" data-field="ziyaret_notlari" data-id="{{ $ziyaret->id }}" data-value="{{ $ziyaret->ziyaret_notlari ?? '' }}">
                                    <div class="flex items-center gap-2">
                                        <div class="max-w-xs truncate">
                                            {{ $ziyaret->ziyaret_notlari ?? '-' }}
                                        </div>
                                        <button type="button" class="notes-edit-btn text-xs text-blue-600 hover:underline">Düzenle</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    Henüz ziyaret kaydı yok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="note-modal" class="note-modal">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Ziyaret Notu</h3>
                <button id="note-modal-close" class="text-gray-500 hover:text-gray-800">✕</button>
            </div>
            <textarea id="note-modal-text" class="w-full h-64 border rounded p-3 text-sm" readonly></textarea>
        </div>
    </div>

    <script>
        // Form ve filtre toggle fonksiyonları
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

        let selectedIds = [];
        const columnStorageKey = 'ziyaretler_column_preferences_v1';

        function toggleColumn(columnName, isVisible) {
            document.querySelectorAll('.col-' + columnName).forEach(el => {
                el.style.display = isVisible ? '' : 'none';
            });
        }

        function saveColumnPreferences() {
            const prefs = {};
            document.querySelectorAll('.column-toggle').forEach(cb => {
                prefs[cb.dataset.column] = cb.checked;
            });
            localStorage.setItem(columnStorageKey, JSON.stringify(prefs));
        }

        function loadColumnPreferences() {
            const raw = localStorage.getItem(columnStorageKey);
            if (!raw) return;
            try {
                const prefs = JSON.parse(raw);
                document.querySelectorAll('.column-toggle').forEach(cb => {
                    if (Object.prototype.hasOwnProperty.call(prefs, cb.dataset.column)) {
                        cb.checked = !!prefs[cb.dataset.column];
                        toggleColumn(cb.dataset.column, cb.checked);
                    }
                });
            } catch (e) {
                console.warn('Sütun tercihleri okunamadı:', e);
            }
        }

        $(document).ready(function() {
            // Select2 başlat
            $('#filter-musteri-select').select2({
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

            // Scroll senkronizasyonu
            const scrollTop = document.getElementById('scroll-top');
            const scrollBottom = document.getElementById('scroll-bottom');
            const table = document.getElementById('ziyaretler-table');
            
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
                    const tbody = document.querySelector('#ziyaretler-table tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr[data-row="1"]'));
                    
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
                        
                        // Tarih sütunları için
                        if (['ziyaret_tarihi'].includes(column)) {
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

            // Sütun menüsü
            const columnBtn = document.getElementById('column-toggle-btn');
            const columnMenu = document.getElementById('column-menu');
            columnBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                columnMenu.classList.toggle('hidden');
            });
            document.addEventListener('click', function(e) {
                if (!columnMenu.contains(e.target) && !columnBtn.contains(e.target)) {
                    columnMenu.classList.add('hidden');
                }
            });
            document.querySelectorAll('.column-toggle').forEach(cb => {
                cb.addEventListener('change', function() {
                    toggleColumn(this.dataset.column, this.checked);
                    saveColumnPreferences();
                });
            });
            loadColumnPreferences();

            function updateSelection() {
                selectedIds = [];
                $('.row-checkbox:checked').each(function() {
                    selectedIds.push($(this).data('id'));
                });

                const hasSelection = selectedIds.length > 0;
                $('#btn-duplicate').prop('disabled', !hasSelection);
                $('#btn-delete').prop('disabled', !hasSelection);

                if (hasSelection) {
                    $('#selection-count').text(selectedIds.length + ' kayıt seçili');
                } else {
                    $('#selection-count').text('');
                }
            }

            $(document).on('change', '.row-checkbox', function() {
                updateSelection();
                const totalCheckboxes = $('.row-checkbox').length;
                const checkedCheckboxes = $('.row-checkbox:checked').length;
                $('#select-all').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
            });

            $('#select-all').on('change', function() {
                $('.row-checkbox').prop('checked', $(this).is(':checked'));
                updateSelection();
            });
        });

        const musteriOptions = @json(\App\Models\Musteri::orderBy('sirket')->get(['id','sirket']));

        function renderMusteriOptions(selectedId) {
            let options = '<option value="">Seçiniz</option>';
            musteriOptions.forEach(item => {
                const selected = selectedId && String(item.id) === String(selectedId) ? 'selected' : '';
                options += `<option value="${item.id}" ${selected}>${item.sirket}</option>`;
            });
            return options;
        }

        function formatDateDisplay(value, isTelefon) {
            if (!value) return '-';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            if (isTelefon) {
                return date.toLocaleDateString('tr-TR');
            }
            // Saat varsa (00:00'dan farklıysa) saat ile göster
            if (date.getHours() !== 0 || date.getMinutes() !== 0) {
                return date.toLocaleString('tr-TR', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
            }
            // Saat yoksa sadece tarih
            return date.toLocaleDateString('tr-TR');
        }

        function buildNewRow() {
            return `
                <tr class="new-row bg-yellow-50">
                    <td class="px-3 py-4 text-center">
                        <input type="checkbox" disabled class="opacity-50">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-medium editable-cell col-ziyaret_ismi" data-field="ziyaret_ismi" data-id="new" data-value="">
                        <span class="text-gray-400">Ziyaret ismi...</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap editable-select col-musteri" data-field="musteri_id" data-id="new" data-value="">
                        <span class="text-gray-400">Müşteri seçiniz...</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap editable-date col-ziyaret_tarihi" data-field="tarih" data-id="new" data-value="">
                        <span class="text-gray-400">Tarih seçiniz...</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap editable-select col-tur" data-field="tur" data-id="new" data-value="">
                        <span class="text-gray-400">Tür seçiniz...</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap editable-select col-durumu" data-field="durumu" data-id="new" data-value="">
                        <span class="text-gray-400">Durum seçiniz...</span>
                    </td>
                    <td class="px-6 py-4 text-sm editable-cell col-ziyaret_notlari" data-field="ziyaret_notlari" data-id="new" data-value="">
                        <span class="text-gray-400">Not...</span>
                    </td>
                </tr>
            `;
        }

        window.addNewRow = function() {
            const tbody = document.querySelector('#ziyaretler-table tbody');
            $(tbody).prepend(buildNewRow());
        };

        window.duplicateSelected = function() {
            if (selectedIds.length === 0) return;
            const row = document.querySelector(`tr[data-id="${selectedIds[0]}"]`);
            if (!row) return;
            const $row = $(row);
            const newRow = $(buildNewRow());
            newRow.find('[data-field="ziyaret_ismi"]').data('value', getRowValue($row, 'ziyaret_ismi') || '').text(getRowValue($row, 'ziyaret_ismi') || 'Ziyaret ismi...');
            newRow.find('[data-field="musteri_id"]').data('value', getRowValue($row, 'musteri_id') || '');
            newRow.find('[data-field="tur"]').data('value', getRowValue($row, 'tur') || '');
            newRow.find('[data-field="durumu"]').data('value', getRowValue($row, 'durumu') || '');
            newRow.find('[data-field="ziyaret_notlari"]').data('value', getRowValue($row, 'ziyaret_notlari') || '').text(getRowValue($row, 'ziyaret_notlari') || 'Not...');
            $('#ziyaretler-table tbody').prepend(newRow);
        };

        window.deleteSelected = function() {
            if (selectedIds.length === 0) return;
            if (!confirm(selectedIds.length + ' kayıt silinecek. Emin misiniz?')) return;

            let completed = 0;
            selectedIds.forEach(id => {
                $.ajax({
                    url: '/ziyaretler/' + id,
                    method: 'POST',
                    data: {
                        _method: 'DELETE'
                    },
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    success: function() {
                        completed++;
                        if (completed === selectedIds.length) {
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        console.error('Silme hatası:', xhr.status, xhr.responseText);
                        alert('Silme işlemi başarısız: ' + (xhr.responseJSON?.message || xhr.statusText));
                    }
                });
            });
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        function getRowValue(row, key) {
            const val = row.data(key);
            if (val !== undefined) return val;
            const dom = row[0];
            if (!dom) return '';
            const dashed = dom.getAttribute('data-' + key.replace(/_/g, '-'));
            if (dashed !== null && dashed !== undefined) return dashed;
            return '';
        }

        function setRowValue(row, key, value) {
            row.data(key, value);
            row.attr('data-' + key.replace(/_/g, '-'), value ?? '');
        }

        function buildUpdatePayload(row, overrides = {}) {
            const normalize = (val) => (val === '' ? null : val);
            const payload = {
                ziyaret_ismi: normalize(getRowValue(row, 'ziyaret_ismi') || ''),
                musteri_id: normalize(getRowValue(row, 'musteri_id') || ''),
                ziyaret_tarihi: normalize(getRowValue(row, 'ziyaret_tarihi') || ''),
                arama_tarihi: normalize(getRowValue(row, 'arama_tarihi') || ''),
                tur: normalize(getRowValue(row, 'tur') || ''),
                durumu: normalize(getRowValue(row, 'durumu') || ''),
                ziyaret_notlari: normalize(getRowValue(row, 'ziyaret_notlari') || '')
            };
            return { ...payload, ...overrides };
        }

        function normalizeDateTimeInput(value, optionalTime = '') {
            if (!value) return null;
            if (value.length === 10) {
                const time = optionalTime && optionalTime.length === 5 ? optionalTime + ':00' : '00:00:00';
                return value + ' ' + time;
            }
            return value.replace('T', ' ') + ':00';
        }

        function renderSelectDisplay(cell, field, newValue, row) {
            if (field === 'musteri_id') {
                const found = musteriOptions.find(item => String(item.id) === String(newValue));
                if (found) {
                    cell.html(`<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">${found.sirket}</span>`);
                    setRowValue(row, 'musteri_id', found.id);
                } else {
                    cell.html('-');
                }
                return;
            }
            if (field === 'tur') {
                if (newValue) {
                    const badgeClass = newValue === 'Ziyaret' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800';
                    cell.html(`<span class="px-2 py-1 text-xs rounded-full ${badgeClass}">${newValue}</span>`);
                    setRowValue(row, 'tur', newValue);
                } else {
                    cell.html('-');
                }
                const dateCell = row.find('[data-field="tarih"]');
                const isTelefon = newValue === 'Telefon';
                const dateVal = isTelefon ? getRowValue(row, 'arama_tarihi') : getRowValue(row, 'ziyaret_tarihi');
                dateCell.data('value', dateVal || '');
                dateCell.html(formatDateDisplay(dateVal, isTelefon));
                return;
            }
            if (field === 'durumu') {
                if (newValue) {
                    let badgeClass = 'bg-green-100 text-green-800';
                    if (newValue === 'Beklemede') badgeClass = 'bg-yellow-100 text-yellow-800';
                    else if (newValue === 'Planlandı') badgeClass = 'bg-blue-100 text-blue-800';
                    cell.html(`<span class="px-2 py-1 text-xs rounded-full ${badgeClass}">${newValue}</span>`);
                    setRowValue(row, 'durumu', newValue);
                } else {
                    cell.html('-');
                }
            }
        }

        function isRowReady(row) {
            const name = (getRowValue(row, 'ziyaret_ismi') || '').trim();
            const ziyaretTarihi = (getRowValue(row, 'ziyaret_tarihi') || '').trim();
            const aramaTarihi = (getRowValue(row, 'arama_tarihi') || '').trim();
            return name.length > 0 && (ziyaretTarihi.length > 0 || aramaTarihi.length > 0);
        }

        function createRow(row, onSuccess, onError) {
            if (row.data('saving') || row.data('created')) return;
            row.data('saving', true);
            const payload = buildUpdatePayload(row, {});
            if (!payload.ziyaret_ismi) {
                row.data('saving', false);
                return;
            }
            $.ajax({
                url: '/ziyaretler',
                method: 'POST',
                data: payload,
                success: function(response) {
                    const newId = response && response.id;
                    if (newId) {
                        finalizeNewRow(row, newId);
                        row.data('created', true);
                        row.data('saving', false);
                        if (onSuccess) onSuccess(newId);
                    } else {
                        location.reload();
                    }
                },
                error: function() {
                    row.data('saving', false);
                    if (onError) onError();
                }
            });
        }

        function focusNextEditableCell(currentCell) {
            const row = currentCell.closest('tr');
            const cells = row.find('.editable-cell, .editable-select, .editable-date');
            const index = cells.index(currentCell);
            let next = cells.eq(index + 1);
            if (!next.length) {
                const nextRow = row.nextAll('tr').find('.editable-cell, .editable-select, .editable-date').first();
                if (nextRow.length) next = nextRow;
            }
            if (next && next.length) {
                setTimeout(() => next.click(), 0);
            }
        }

        function finalizeNewRow(row, newId) {
            row.removeClass('new-row');
            row.attr('data-id', newId);
            row.data('id', newId);
            row.find('[data-id="new"]').attr('data-id', newId).data('id', newId);
            const checkbox = row.find('input[type="checkbox"]');
            checkbox.prop('disabled', false).removeClass('opacity-50').addClass('row-checkbox').attr('data-id', newId);
        }

        $(document).on('click', '.editable-cell:not(.editing)', function(e) {
            e.stopPropagation();
            const cell = $(this);
            const field = cell.data('field');
            const id = cell.data('id');
            const currentValue = cell.data('value') || '';
            const row = cell.closest('tr');

            if (field === 'ziyaret_notlari' && !cell.data('forceEdit')) {
                const text = currentValue || '';
                openNoteModal(text);
                return;
            }
            cell.data('forceEdit', false);

            cell.addClass('editing');
            const originalContent = cell.html();
            let saved = false;

            cell.html(`<input type="text" class="w-full px-2 py-1 border rounded text-sm" value="${currentValue}" />`);
            const input = cell.find('input');
            input.focus();

            function saveEdit() {
                if (saved) return;
                saved = true;
                const newValue = input.val().trim();
                if (id === 'new') {
                    setRowValue(row, field, newValue);
                    cell.html(newValue || '-');
                    cell.removeClass('editing');
                    if (isRowReady(row)) {
                        createRow(row, function(newId) {
                            cell.data('id', newId);
                            row.data('id', newId);
                        }, function() {
                            alert('Kayıt oluşturulamadı!');
                            cell.html(originalContent);
                            cell.removeClass('editing');
                        });
                    }
                } else {
                    $.ajax({
                        url: '/ziyaretler/' + id,
                        method: 'POST',
                        data: {
                            _method: 'PUT',
                            [field]: newValue
                        },
                        success: function() {
                            cell.data('value', newValue);
                            setRowValue(row, field, newValue);
                            cell.html(newValue || '-');
                            cell.removeClass('editing');
                        },
                        error: function(xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseText || '');
                            console.error('Kaydedilemedi:', xhr.status, xhr.responseText);
                            alert('Kaydedilemedi! ' + msg);
                            cell.html(originalContent);
                            cell.removeClass('editing');
                        }
                    });
                }
            }

            input.on('keypress', function(e) {
                if (e.which === 13) saveEdit();
            });
            input.on('keydown', function(e) {
                if (e.which === 27) {
                    cell.html(originalContent);
                    cell.removeClass('editing');
                }
                if (e.which === 9) {
                    e.preventDefault();
                    saveEdit();
                    focusNextEditableCell(cell);
                }
            });
            input.on('blur', function() {
                if (!saved) saveEdit();
            });
        });

        $(document).on('click', '.notes-edit-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const cell = $(this).closest('td.editable-cell');
            cell.data('forceEdit', true);
            cell.trigger('click');
        });

        $(document).on('click', '.editable-select:not(.editing)', function(e) {
            e.stopPropagation();
            const cell = $(this);
            const field = cell.data('field');
            const id = cell.data('id');
            const currentValue = cell.data('value') || '';
            const row = cell.closest('tr');

            cell.addClass('editing');
            const originalContent = cell.html();

            let options = '';
            if (field === 'musteri_id') {
                options = renderMusteriOptions(currentValue);
            } else if (field === 'tur') {
                options = `
                    <option value="">Seçiniz</option>
                    <option value="Ziyaret" ${currentValue === 'Ziyaret' ? 'selected' : ''}>Ziyaret</option>
                    <option value="Telefon" ${currentValue === 'Telefon' ? 'selected' : ''}>Telefon</option>
                `;
            } else if (field === 'durumu') {
                options = `
                    <option value="">Seçiniz</option>
                    <option value="Beklemede" ${currentValue === 'Beklemede' ? 'selected' : ''}>Beklemede</option>
                    <option value="Planlandı" ${currentValue === 'Planlandı' ? 'selected' : ''}>Planlandı</option>
                    <option value="Tamamlandı" ${currentValue === 'Tamamlandı' ? 'selected' : ''}>Tamamlandı</option>
                `;
            }

            cell.html(`<select class="w-full px-2 py-1 border rounded text-sm">${options}</select>`);
            const select = cell.find('select');
            select.focus();
            let saved = false;

            function saveSelect() {
                if (saved) return;
                saved = true;
                const newValue = select.val();
                if (id === 'new') {
                    setRowValue(row, field, newValue);
                    renderSelectDisplay(cell, field, newValue, row);
                    cell.removeClass('editing');
                    if (isRowReady(row)) {
                        createRow(row, function(newId) {
                            cell.data('id', newId);
                            row.data('id', newId);
                        }, function() {
                            alert('Kayıt oluşturulamadı!');
                            cell.html(originalContent);
                            cell.removeClass('editing');
                        });
                    }
                } else {
                    $.ajax({
                        url: '/ziyaretler/' + id,
                        method: 'POST',
                        data: {
                            _method: 'PUT',
                            [field]: newValue
                        },
                        success: function() {
                            cell.data('value', newValue);
                            renderSelectDisplay(cell, field, newValue, row);
                            cell.removeClass('editing');
                        },
                        error: function(xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseText || '');
                            console.error('Kaydedilemedi:', xhr.status, xhr.responseText);
                            alert('Kaydedilemedi! ' + msg);
                            cell.html(originalContent);
                            cell.removeClass('editing');
                        }
                    });
                }
            }

            select.on('change', saveSelect);
            select.on('keydown', function(e) {
                if (e.which === 27) {
                    cell.html(originalContent);
                    cell.removeClass('editing');
                }
                if (e.which === 13) {
                    e.preventDefault();
                    saveSelect();
                }
                if (e.which === 9) {
                    e.preventDefault();
                    saveSelect();
                    focusNextEditableCell(cell);
                }
            });
            select.on('blur', function() {
                if (!saved) saveSelect();
            });
        });

        $(document).on('click', '.editable-date:not(.editing)', function(e) {
            e.stopPropagation();
            const cell = $(this);
            const id = cell.data('id');
            const currentValue = cell.data('value') || '';
            const row = cell.closest('tr');
            const tur = getRowValue(row, 'tur') || '';
            const isTelefon = tur === 'Telefon';

            cell.addClass('editing');
            const originalContent = cell.html();
            let saved = false;

            const isNewRow = id === 'new';
            let valueForInput = '';
            let dateForInput = '';
            let timeForInput = '';
            if (currentValue) {
                const date = new Date(currentValue);
                if (!Number.isNaN(date.getTime())) {
                    valueForInput = date.toISOString().slice(0, 16);
                    dateForInput = date.toISOString().slice(0, 10);
                    timeForInput = date.toISOString().slice(11, 16);
                }
            }

            if (isNewRow) {
                cell.html(`
                    <div class="flex items-center gap-2">
                        <input type="date" class="date-input w-full px-2 py-1 border rounded text-sm" value="${dateForInput}" />
                        <input type="time" class="time-input w-28 px-2 py-1 border rounded text-sm" value="${timeForInput}" />
                    </div>
                `);
            } else {
                cell.html(`<input type="datetime-local" class="w-full px-2 py-1 border rounded text-sm" value="${valueForInput}" />`);
            }

            const input = isNewRow ? cell.find('.date-input') : cell.find('input');
            input.focus();

            function saveDate() {
                if (saved) return;
                saved = true;
                const rawValue = isNewRow ? cell.find('.date-input').val() : input.val();
                const rawTime = isNewRow ? cell.find('.time-input').val() : '';
                const newValue = normalizeDateTimeInput(rawValue, rawTime);
                
                // Boş tarih gönderme
                if (!newValue || newValue.trim() === '') {
                    cell.html(originalContent);
                    cell.removeClass('editing');
                    return;
                }
                
                const field = isTelefon ? 'arama_tarihi' : 'ziyaret_tarihi';

                if (id === 'new') {
                    if (isTelefon) {
                        setRowValue(row, 'arama_tarihi', newValue);
                    } else {
                        setRowValue(row, 'ziyaret_tarihi', newValue);
                    }
                    cell.html(formatDateDisplay(newValue, isTelefon));
                    cell.removeClass('editing');
                    if (isRowReady(row)) {
                        createRow(row, function(newId) {
                            cell.data('id', newId);
                            row.data('id', newId);
                        }, function() {
                            alert('Kayıt oluşturulamadı!');
                            cell.html(originalContent);
                            cell.removeClass('editing');
                        });
                    }
                } else {
                    $.ajax({
                        url: '/ziyaretler/' + id,
                        method: 'POST',
                        data: {
                            _method: 'PUT',
                            [field]: newValue
                        },
                        success: function() {
                            cell.data('value', newValue);
                            if (isTelefon) {
                                setRowValue(row, 'arama_tarihi', newValue);
                            } else {
                                setRowValue(row, 'ziyaret_tarihi', newValue);
                            }
                            cell.html(formatDateDisplay(newValue, isTelefon));
                            cell.removeClass('editing');
                        },
                        error: function(xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseText || '');
                            console.error('Kaydedilemedi:', xhr.status, xhr.responseText);
                            alert('Kaydedilemedi! ' + msg);
                            cell.html(originalContent);
                            cell.removeClass('editing');
                        }
                    });
                }
            }

            input.on('keypress', function(e) {
                if (e.which === 13) saveDate();
            });
            if (isNewRow) {
                cell.find('.time-input').on('keypress', function(e) {
                    if (e.which === 13) saveDate();
                });
            }
            input.on('keydown', function(e) {
                if (e.which === 27) {
                    cell.html(originalContent);
                    cell.removeClass('editing');
                }
                if (isNewRow && e.which === 9) {
                    // Tarihten Tab ile saate geçişte kaydetme yapma
                    return;
                }
                if (e.which === 9) {
                    e.preventDefault();
                    saveDate();
                    focusNextEditableCell(cell);
                }
            });
            if (isNewRow) {
                cell.find('.time-input').on('keydown', function(e) {
                    if (e.which === 27) {
                        cell.html(originalContent);
                        cell.removeClass('editing');
                    }
                    if (e.which === 9) {
                        e.preventDefault();
                        saveDate();
                        focusNextEditableCell(cell);
                    }
                });
            }
            input.on('blur', function() {
                setTimeout(function() {
                    if (!saved && cell.find(':focus').length === 0) saveDate();
                }, 0);
            });
            if (isNewRow) {
                cell.find('.time-input').on('blur', function() {
                    setTimeout(function() {
                        if (!saved && cell.find(':focus').length === 0) saveDate();
                    }, 0);
                });
            }
        });

        const noteModal = document.getElementById('note-modal');
        const noteModalText = document.getElementById('note-modal-text');
        const noteModalClose = document.getElementById('note-modal-close');

        function openNoteModal(text) {
            noteModalText.value = text || '';
            noteModal.classList.add('open');
        }

        function closeNoteModal() {
            noteModal.classList.remove('open');
        }

        noteModalClose.addEventListener('click', closeNoteModal);
        noteModal.addEventListener('click', function(e) {
            if (e.target === noteModal) closeNoteModal();
        });
    </script>
</body>
</html>
