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
        .toolbar-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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

        <!-- Form -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 flex justify-between items-center cursor-pointer" onclick="toggleForm()">
                <h2 class="text-xl font-bold">Yeni Ziyaret Ekle</h2>
                <span id="form-toggle-icon" class="text-2xl transform transition-transform">▼</span>
            </div>
            <div id="ziyaret-ekle-form" style="display: none;">
                <form method="POST" action="/ziyaretler" class="space-y-4 px-6 pb-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Ziyaret İsmi *</label>
                        <input type="text" name="ziyaret_ismi" required class="w-full border rounded px-3 py-2">
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
                        <label class="block text-sm font-medium mb-1">Ziyaret Tarihi</label>
                        <input type="datetime-local" name="ziyaret_tarihi" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Arama Tarihi</label>
                        <input type="date" name="arama_tarihi" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Tür</label>
                        <select name="tur" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="Ziyaret">Ziyaret</option>
                            <option value="Telefon">Telefon</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Durumu</label>
                        <select name="durumu" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="Beklemede">Beklemede</option>
                            <option value="Planlandı">Planlandı</option>
                            <option value="Tamamlandı">Tamamlandı</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Ziyaret Notları</label>
                    <textarea name="ziyaret_notlari" rows="4" class="w-full border rounded px-3 py-2"></textarea>
                </div>
                
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    Ziyaret Ekle
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
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="ziyaret_ismi">Ziyaret <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="musteri">Müşteri <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="ziyaret_tarihi">Ziyaret Tarihi <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="tur">Tür <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="durumu">Durum <span class="sort-icon"></span></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notlar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
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
                            
                            $ziyaretler = $query->latest('ziyaret_tarihi')->get();
                        @endphp
                        
                        @forelse($ziyaretler as $ziyaret)
                            <tr data-row="1"
                                data-id="{{ $ziyaret->id }}"
                                data-ziyaret_ismi="{{ $ziyaret->ziyaret_ismi }}" 
                                data-musteri="{{ $ziyaret->musteri ? $ziyaret->musteri->sirket : '' }}" 
                                data-musteri_id="{{ $ziyaret->musteri_id ?? '' }}"
                                data-ziyaret_tarihi="{{ $ziyaret->ziyaret_tarihi }}" 
                                data-arama_tarihi="{{ $ziyaret->arama_tarihi }}"
                                data-tur="{{ $ziyaret->tur ?? '' }}" 
                                data-durumu="{{ $ziyaret->durumu ?? '' }}"
                                data-ziyaret_notlari="{{ $ziyaret->ziyaret_notlari ?? '' }}">
                                <td class="px-3 py-4 text-center">
                                    <input type="checkbox" class="row-checkbox cursor-pointer" data-id="{{ $ziyaret->id }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $ziyaret->ziyaret_ismi }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($ziyaret->musteri)
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ $ziyaret->musteri->sirket }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($ziyaret->tur == 'Telefon' && $ziyaret->arama_tarihi)
                                        {{ \Carbon\Carbon::parse($ziyaret->arama_tarihi)->format('d.m.Y') }}
                                    @elseif($ziyaret->ziyaret_tarihi)
                                        {{ $ziyaret->ziyaret_tarihi->format('d.m.Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($ziyaret->tur)
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $ziyaret->tur == 'Ziyaret' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $ziyaret->tur }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                <td class="px-6 py-4 text-sm">
                                    <div class="max-w-xs truncate">
                                        {{ $ziyaret->ziyaret_notlari ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="/ziyaretler/{{ $ziyaret->id }}/edit" class="text-blue-600 hover:text-blue-800 mr-3">
                                        ✏️ Düzenle
                                    </a>
                                    <form action="/ziyaretler/{{ $ziyaret->id }}" method="POST" class="inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            🗑️ Sil
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    Henüz ziyaret kaydı yok.
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
            const form = document.getElementById('ziyaret-ekle-form');
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

        let selectedIds = [];

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

        function buildNewRow(prefill = {}) {
            const turVal = prefill.tur || '';
            const durumuVal = prefill.durumu || '';
            const ziyaretTarihiVal = prefill.ziyaret_tarihi || '';
            const aramaTarihiVal = prefill.arama_tarihi || '';
            const dateVal = turVal === 'Telefon' ? aramaTarihiVal : ziyaretTarihiVal;

            return `
                <tr class="new-row bg-yellow-50">
                    <td class="px-3 py-4 text-center">
                        <input type="checkbox" disabled class="opacity-50">
                    </td>
                    <td class="px-6 py-4">
                        <input type="text" class="w-full border rounded px-2 py-1" placeholder="Ziyaret ismi..." value="${prefill.ziyaret_ismi || ''}">
                    </td>
                    <td class="px-6 py-4">
                        <select class="w-full border rounded px-2 py-1">
                            ${renderMusteriOptions(prefill.musteri_id)}
                        </select>
                    </td>
                    <td class="px-6 py-4">
                        <input type="datetime-local" class="w-full border rounded px-2 py-1" value="${dateVal || ''}">
                    </td>
                    <td class="px-6 py-4">
                        <select class="w-full border rounded px-2 py-1">
                            <option value="">Seçiniz</option>
                            <option value="Ziyaret" ${turVal === 'Ziyaret' ? 'selected' : ''}>Ziyaret</option>
                            <option value="Telefon" ${turVal === 'Telefon' ? 'selected' : ''}>Telefon</option>
                        </select>
                    </td>
                    <td class="px-6 py-4">
                        <select class="w-full border rounded px-2 py-1">
                            <option value="">Seçiniz</option>
                            <option value="Beklemede" ${durumuVal === 'Beklemede' ? 'selected' : ''}>Beklemede</option>
                            <option value="Planlandı" ${durumuVal === 'Planlandı' ? 'selected' : ''}>Planlandı</option>
                            <option value="Tamamlandı" ${durumuVal === 'Tamamlandı' ? 'selected' : ''}>Tamamlandı</option>
                        </select>
                    </td>
                    <td class="px-6 py-4">
                        <input type="text" class="w-full border rounded px-2 py-1" placeholder="Not..." value="${prefill.ziyaret_notlari || ''}">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded mr-2" onclick="saveNewRow(this)">Kaydet</button>
                        <button class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-1 rounded" onclick="cancelNewRow(this)">Vazgeç</button>
                    </td>
                </tr>
            `;
        }

        window.addNewRow = function() {
            const tbody = document.querySelector('#ziyaretler-table tbody');
            const newRowHtml = buildNewRow();
            $(tbody).prepend(newRowHtml);
        };

        window.duplicateSelected = function() {
            if (selectedIds.length === 0) return;
            const row = document.querySelector(`tr[data-id="${selectedIds[0]}"]`);
            if (!row) return;
            const prefill = {
                ziyaret_ismi: row.dataset.ziyaret_ismi || '',
                musteri_id: row.dataset.musteri_id || '',
                ziyaret_tarihi: row.dataset.ziyaret_tarihi ? row.dataset.ziyaret_tarihi.replace(' ', 'T').slice(0, 16) : '',
                arama_tarihi: row.dataset.arama_tarihi || '',
                tur: row.dataset.tur || '',
                durumu: row.dataset.durumu || '',
                ziyaret_notlari: row.dataset.ziyaret_notlari || ''
            };
            const tbody = document.querySelector('#ziyaretler-table tbody');
            const newRowHtml = buildNewRow(prefill);
            $(tbody).prepend(newRowHtml);
        };

        window.deleteSelected = function() {
            if (selectedIds.length === 0) return;
            if (!confirm(selectedIds.length + ' kayıt silinecek. Emin misiniz?')) return;

            let completed = 0;
            selectedIds.forEach(id => {
                $.ajax({
                    url: '/ziyaretler/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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

        window.saveNewRow = function(btn) {
            const row = btn.closest('tr');
            const inputs = row.querySelectorAll('input, select');
            const [ziyaretIsmiEl, musteriEl, tarihEl, turEl, durumuEl, notlarEl] = inputs;

            const ziyaret_ismi = ziyaretIsmiEl.value.trim();
            const musteri_id = musteriEl.value || null;
            const tarihVal = tarihEl.value;
            const tur = turEl.value || null;
            const durumu = durumuEl.value || null;
            const ziyaret_notlari = notlarEl.value || null;

            if (!ziyaret_ismi) {
                alert('Ziyaret ismi zorunlu.');
                return;
            }

            const payload = {
                ziyaret_ismi,
                musteri_id,
                tur,
                durumu,
                ziyaret_notlari
            };

            if (tur === 'Telefon') {
                payload.arama_tarihi = tarihVal ? tarihVal.split('T')[0] : null;
            } else {
                payload.ziyaret_tarihi = tarihVal || null;
            }

            $.ajax({
                url: '/ziyaretler',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                data: payload,
                success: function() {
                    location.reload();
                },
                error: function() {
                    alert('Kayıt eklenemedi. Lütfen tekrar deneyin.');
                }
            });
        };

        window.cancelNewRow = function(btn) {
            const row = btn.closest('tr');
            row.remove();
        };
    </script>
</body>
</html>
