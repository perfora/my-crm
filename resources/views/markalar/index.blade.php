<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markalar - CRM</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sortable { cursor: pointer; user-select: none; }
        .sortable:hover { background-color: #f3f4f6; }
        .editable-cell:hover { background-color: #fef3c7 !important; }
        .editing { padding: 0 !important; }
        .toolbar-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
</head>
<body class="bg-gray-100">
    @include('layouts.nav')

    <div class="container mx-auto px-4 py-8">
        @php
            $toplamMarka = \App\Models\Marka::count();
            $markalar = \App\Models\Marka::latest()->get();
        @endphp

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Markalar</h1>
            <span class="text-lg font-semibold text-gray-600">Toplam: {{ $toplamMarka }}</span>
        </div>

        @if(session('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 flex justify-between items-center cursor-pointer" onclick="toggleFilters()">
                <h2 class="text-xl font-bold">Filtreler</h2>
                <span id="filter-toggle-icon" class="text-2xl transform transition-transform">▼</span>
            </div>
            <div id="filters-form" style="display: none;">
                <div class="px-6 pb-6">
                    <label class="block text-sm font-medium mb-1">Marka Adı</label>
                    <input type="text" id="filter-name" class="w-full md:w-96 border rounded px-3 py-2" placeholder="Ara...">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
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
                            <span>📊 Sütunlar</span><span id="column-arrow">▼</span>
                        </button>
                        <div id="column-menu" class="hidden absolute right-0 mt-2 w-56 bg-white border rounded-lg shadow-lg z-50 p-3">
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="name" checked> Marka Adı
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="checkbox" class="column-toggle" data-column="created_at" checked> Tarih
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="markalar-table" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-center">
                                <input type="checkbox" id="select-all" class="cursor-pointer">
                            </th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase col-name" data-column="name">Marka Adı <span class="sort-icon"></span></th>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase col-created_at" data-column="created_at">Tarih <span class="sort-icon"></span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($markalar as $marka)
                            <tr data-row="1" data-id="{{ $marka->id }}" data-name="{{ mb_strtolower($marka->name) }}" data-created_at="{{ $marka->created_at }}">
                                <td class="px-3 py-4 text-center">
                                    <input type="checkbox" class="row-checkbox cursor-pointer" data-id="{{ $marka->id }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium editable-cell col-name" data-field="name" data-id="{{ $marka->id }}" data-value="{{ $marka->name }}">
                                    <a href="/markalar/{{ $marka->id }}" class="text-blue-600 hover:underline">{{ $marka->name }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 col-created_at">
                                    {{ $marka->created_at ? $marka->created_at->format('d.m.Y') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr class="empty-row">
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">Henüz marka kaydı yok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let selectedIds = [];
        const sortDirection = {};

        function toggleFilters() {
            const filters = document.getElementById('filters-form');
            const icon = document.getElementById('filter-toggle-icon');
            const isHidden = filters.style.display === 'none';
            filters.style.display = isHidden ? 'block' : 'none';
            icon.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
        }

        function updateSelection() {
            selectedIds = [...document.querySelectorAll('.row-checkbox:checked')].map(cb => Number(cb.dataset.id));
            document.getElementById('btn-duplicate').disabled = selectedIds.length === 0;
            document.getElementById('btn-delete').disabled = selectedIds.length === 0;
            document.getElementById('selection-count').textContent = selectedIds.length ? `${selectedIds.length} kayıt seçili` : '';
        }

        function addNewRow() {
            const tbody = document.querySelector('#markalar-table tbody');
            const empty = tbody.querySelector('.empty-row');
            if (empty) empty.remove();

            const row = document.createElement('tr');
            row.className = 'new-row bg-yellow-50';
            row.innerHTML = `
                <td class="px-3 py-4 text-center"><input type="checkbox" disabled class="opacity-50"></td>
                <td class="px-6 py-4 whitespace-nowrap font-medium editable-cell col-name" data-field="name" data-id="new" data-value=""><span class="text-gray-400">Marka adı...</span></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 col-created_at">-</td>
            `;
            tbody.prepend(row);
        }

        async function createMarka(name, row, cell) {
            const resp = await fetch('/markalar', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name })
            });
            const data = await resp.json();
            if (!resp.ok || !data.success) throw new Error(data.message || 'Kayıt hatası');

            row.dataset.id = data.data.id;
            row.dataset.name = (data.data.name || '').toLowerCase();
            row.dataset.created_at = data.data.created_at || '';
            row.classList.remove('new-row', 'bg-yellow-50');

            cell.dataset.id = data.data.id;
            cell.dataset.value = data.data.name;
            cell.innerHTML = `<a href="/markalar/${data.data.id}" class="text-blue-600 hover:underline">${data.data.name}</a>`;

            const checkboxCell = row.querySelector('td input[type="checkbox"]');
            checkboxCell.disabled = false;
            checkboxCell.classList.remove('opacity-50');
            checkboxCell.classList.add('row-checkbox');
            checkboxCell.dataset.id = data.data.id;

            const dateCell = row.querySelector('.col-created_at');
            const created = data.data.created_at ? new Date(data.data.created_at) : null;
            dateCell.textContent = created && !Number.isNaN(created.getTime())
                ? created.toLocaleDateString('tr-TR')
                : '-';
        }

        async function updateMarka(id, name, cell, row) {
            const resp = await fetch(`/markalar/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _method: 'PUT', name })
            });
            const data = await resp.json();
            if (!resp.ok || !data.success) throw new Error(data.message || 'Güncelleme hatası');

            cell.dataset.value = name;
            row.dataset.name = name.toLowerCase();
            cell.innerHTML = `<a href="/markalar/${id}" class="text-blue-600 hover:underline">${name}</a>`;
        }

        async function deleteSelected() {
            if (!selectedIds.length) return;
            if (!confirm(`${selectedIds.length} kayıt silinecek. Emin misiniz?`)) return;

            for (const id of selectedIds) {
                const resp = await fetch(`/markalar/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                });
                if (!resp.ok) {
                    alert('Silme işlemi başarısız oldu.');
                    return;
                }
            }
            location.reload();
        }

        async function duplicateSelected() {
            if (!selectedIds.length) return;
            const firstRow = document.querySelector(`tr[data-id="${selectedIds[0]}"]`);
            if (!firstRow) return;
            const originalName = firstRow.querySelector('[data-field="name"]').dataset.value || '';
            const copyName = `${originalName} Kopya`;
            const resp = await fetch('/markalar', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: copyName })
            });
            if (!resp.ok) {
                alert('Kopyalama başarısız.');
                return;
            }
            location.reload();
        }

        function focusNextEditableCell(cell) {
            const row = cell.closest('tr');
            const editableCells = [...row.querySelectorAll('.editable-cell')];
            const idx = editableCells.indexOf(cell);
            if (idx >= 0 && editableCells[idx + 1]) editableCells[idx + 1].click();
        }

        function handleEditableCell(cell) {
            if (cell.classList.contains('editing')) return;
            cell.classList.add('editing');
            const row = cell.closest('tr');
            const id = cell.dataset.id;
            const currentValue = cell.dataset.value || '';
            const original = cell.innerHTML;
            cell.innerHTML = `<input type="text" class="w-full px-2 py-1 border rounded text-sm" value="${currentValue.replace(/"/g, '&quot;')}">`;
            const input = cell.querySelector('input');
            input.focus();
            let saved = false;

            const save = async () => {
                if (saved) return;
                saved = true;
                const newValue = input.value.trim();
                if (!newValue) {
                    cell.innerHTML = original;
                    cell.classList.remove('editing');
                    return;
                }

                try {
                    if (id === 'new') {
                        await createMarka(newValue, row, cell);
                    } else {
                        await updateMarka(id, newValue, cell, row);
                    }
                } catch (e) {
                    alert('Kaydedilemedi!');
                    cell.innerHTML = original;
                } finally {
                    cell.classList.remove('editing');
                }
            };

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') save();
                if (e.key === 'Escape') {
                    saved = true;
                    cell.innerHTML = original;
                    cell.classList.remove('editing');
                }
                if (e.key === 'Tab') {
                    e.preventDefault();
                    save();
                    focusNextEditableCell(cell);
                }
            });
            input.addEventListener('blur', () => { if (!saved) save(); });
        }

        function applyFilters() {
            const keyword = (document.getElementById('filter-name').value || '').toLowerCase();
            document.querySelectorAll('#markalar-table tbody tr[data-row="1"]').forEach(row => {
                const name = row.dataset.name || '';
                row.style.display = name.includes(keyword) ? '' : 'none';
            });
        }

        function sortByColumn(header) {
            const column = header.dataset.column;
            const tbody = document.querySelector('#markalar-table tbody');
            const rows = [...tbody.querySelectorAll('tr[data-row="1"]')];
            sortDirection[column] = sortDirection[column] === 'asc' ? 'desc' : 'asc';
            const isAsc = sortDirection[column] === 'asc';

            document.querySelectorAll('.sort-icon').forEach(icon => icon.textContent = '');
            header.querySelector('.sort-icon').textContent = isAsc ? ' ▲' : ' ▼';

            rows.sort((a, b) => {
                const av = a.dataset[column] || '';
                const bv = b.dataset[column] || '';
                if (column === 'created_at') {
                    const ad = new Date(av || '1970-01-01');
                    const bd = new Date(bv || '1970-01-01');
                    return isAsc ? ad - bd : bd - ad;
                }
                return isAsc ? av.localeCompare(bv, 'tr') : bv.localeCompare(av, 'tr');
            });
            rows.forEach(r => tbody.appendChild(r));
        }

        function toggleColumn(column, show) {
            document.querySelectorAll(`.col-${column}`).forEach(el => {
                el.style.display = show ? '' : 'none';
            });
        }

        document.addEventListener('click', (e) => {
            const colBtn = document.getElementById('column-toggle-btn');
            const colMenu = document.getElementById('column-menu');
            if (colBtn.contains(e.target)) {
                colMenu.classList.toggle('hidden');
                return;
            }
            if (!colMenu.contains(e.target)) colMenu.classList.add('hidden');
        });

        document.getElementById('filter-name').addEventListener('input', applyFilters);
        document.getElementById('select-all').addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
            updateSelection();
        });

        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('row-checkbox')) {
                updateSelection();
            }
            if (e.target.classList.contains('column-toggle')) {
                toggleColumn(e.target.dataset.column, e.target.checked);
            }
        });

        document.addEventListener('click', (e) => {
            if (e.target.closest('.editable-cell')) {
                handleEditableCell(e.target.closest('.editable-cell'));
            }
            if (e.target.closest('.sortable')) {
                sortByColumn(e.target.closest('.sortable'));
            }
        });
    </script>
</body>
</html>

