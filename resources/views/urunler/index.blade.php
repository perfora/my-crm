<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürünler - CRM</title>
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
        .editable-cell, .editable-select {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .editable-cell:hover, .editable-select:hover {
            background-color: #fef3c7 !important;
        }
    </style>
</head>
<body class="bg-gray-50">
    @include('layouts.nav')

    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Ürünler</h1>
            <button onclick="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                + Yeni Ürün
            </button>
        </div>

        @if(session('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün Adı</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marka</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Kodu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Son Alış</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kar Oranı</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($urunler as $urun)
                        <tr class="hover:bg-gray-50" data-id="{{ $urun->id }}">
                            <td class="px-6 py-4 whitespace-nowrap font-semibold editable-cell" data-field="urun_adi">
                                {{ $urun->urun_adi }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap editable-select" data-field="marka_id">
                                {{ $urun->marka->marka_adi ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="kategori">
                                {{ $urun->kategori ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="stok_kodu">
                                {{ $urun->stok_kodu ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="son_alis_fiyat">
                                {{ $urun->son_alis_fiyat ? number_format($urun->son_alis_fiyat, 2) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap editable-cell" data-field="ortalama_kar_orani">
                                {{ $urun->ortalama_kar_orani ?? 25 }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button onclick="deleteUrun({{ $urun->id }})" class="text-red-600 hover:text-red-900">Sil</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                Henüz ürün eklenmemiş.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="urunModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Yeni Ürün</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="urunForm" onsubmit="handleSubmit(event)">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ürün Adı *</label>
                        <input type="text" id="urun_adi" name="urun_adi" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Marka</label>
                        <select id="markaSelect" name="marka_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Marka seçin...</option>
                            @foreach($markalar as $marka)
                                <option value="{{ $marka->id }}">{{ $marka->marka_adi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                        <input type="text" id="kategori" name="kategori"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stok Kodu</label>
                        <input type="text" id="stok_kodu" name="stok_kodu"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Son Alış Fiyatı</label>
                        <input type="number" step="0.01" min="0" id="son_alis_fiyat" name="son_alis_fiyat"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ortalama Kar Oranı (%)</label>
                        <input type="number" min="0" id="ortalama_kar_orani" name="ortalama_kar_orani" value="25"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notlar</label>
                        <textarea id="notlar" name="notlar" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        İptal
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const markalar = @json($markalar);

        $(document).ready(function() {
            $('#markaSelect').select2({
                placeholder: 'Marka seçin...',
                language: { noResults: () => "Sonuç bulunamadı", searching: () => "Aranıyor..." }
            });
        });

        function openAddModal() {
            document.getElementById('urunForm').reset();
            $('#markaSelect').val('').trigger('change');
            document.getElementById('urunModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('urunModal').classList.add('hidden');
        }

        function handleSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            fetch('/urunler', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert('Hata: ' + (result.message || 'Kayıt yapılamadı'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu!');
            });
        }

        function deleteUrun(id) {
            if (!confirm('Bu ürünü silmek istediğinize emin misiniz?')) {
                return;
            }
            
            fetch(`/urunler/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert('Hata: ' + (result.message || 'Silme işlemi yapılamadı'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu!');
            });
        }

        // Inline editing
        $(document).on('click', '.editable-cell', function() {
            if ($(this).hasClass('editing')) return;
            
            const cell = $(this);
            const field = cell.data('field');
            const currentValue = cell.text().trim();
            let value = currentValue === '-' ? '' : currentValue;
            
            // Kar oranı ve fiyat için % ve formatı temizle
            if (field === 'ortalama_kar_orani') {
                value = value.replace('%', '');
            } else if (field === 'son_alis_fiyat') {
                value = value.replace(/[^0-9.]/g, '');
            }
            
            const row = cell.closest('tr');
            const id = row.data('id');
            const originalContent = cell.html();
            
            const inputType = field === 'son_alis_fiyat' ? 'number' : 'text';
            const step = field === 'son_alis_fiyat' ? '0.01' : '';
            
            cell.addClass('editing').html(`
                <input type="${inputType}" ${step ? `step="${step}"` : ''} 
                    class="w-full px-2 py-1 border border-blue-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    value="${value}">
            `);
            
            const input = cell.find('input');
            input.focus();
            
            input.on('blur', function() {
                const newValue = $(this).val().trim();
                
                if (newValue === value) {
                    cell.removeClass('editing').html(originalContent);
                    return;
                }
                
                const data = { [field]: newValue || null };
                
                $.ajax({
                    url: `/urunler/${id}`,
                    method: 'PUT',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        let displayValue = newValue === '' ? '-' : newValue;
                        if (field === 'ortalama_kar_orani' && newValue) {
                            displayValue = newValue + '%';
                        } else if (field === 'son_alis_fiyat' && newValue) {
                            displayValue = parseFloat(newValue).toFixed(2);
                        }
                        cell.removeClass('editing').html(displayValue);
                    },
                    error: function() {
                        alert('Kaydedilemedi!');
                        cell.removeClass('editing').html(originalContent);
                    }
                });
            });
            
            input.on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $(this).blur();
                } else if (e.key === 'Escape') {
                    cell.removeClass('editing').html(originalContent);
                }
            });
        });

        // Marka inline editing
        $(document).on('click', '.editable-select', function() {
            if ($(this).hasClass('editing')) return;
            
            const cell = $(this);
            const currentValue = cell.text().trim();
            const row = cell.closest('tr');
            const id = row.data('id');
            const originalContent = cell.html();
            
            // Mevcut markayı bul
            const currentMarka = markalar.find(m => m.marka_adi === currentValue);
            
            let options = '<option value="">Marka seç...</option>';
            markalar.forEach(m => {
                const selected = m.id === currentMarka?.id ? 'selected' : '';
                options += `<option value="${m.id}" ${selected}>${m.marka_adi}</option>`;
            });
            
            cell.addClass('editing').html(`
                <select class="marka-inline-select w-full px-2 py-1 border border-blue-500 rounded">
                    ${options}
                </select>
            `);
            
            const select = cell.find('select');
            select.focus();
            
            select.on('change blur', function() {
                const newValue = $(this).val();
                const selectedMarka = newValue ? markalar.find(m => m.id == newValue) : null;
                
                $.ajax({
                    url: `/urunler/${id}`,
                    method: 'PUT',
                    data: { marka_id: newValue || null },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        const displayValue = selectedMarka?.marka_adi || '-';
                        cell.removeClass('editing').html(displayValue);
                    },
                    error: function() {
                        alert('Kaydedilemedi!');
                        cell.removeClass('editing').html(originalContent);
                    }
                });
            });
        });
    </script>
</body>
</html>
