<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tedarikçi Fiyatları - CRM</title>
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
        #pasteArea {
            min-height: 150px;
            font-family: monospace;
            white-space: pre;
            overflow-wrap: normal;
        }
        #pasteArea:empty:before {
            content: "Excel'den kopyaladığınız verileri buraya yapıştırın (Ctrl+V)...\n\nBeklenen format:\nÜrün Adı    Birim Fiyat    Adet    Para Birimi\nÖrnek Ürün 1    100    5    TL\nÖrnek Ürün 2    200    10    USD";
            color: #9ca3af;
        }
        .editable-cell {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .editable-cell:hover {
            background-color: #fef3c7 !important;
        }
    </style>
</head>
<body class="bg-gray-50">
    @include('layouts.nav')

    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Tedarikçi Fiyatları</h1>
            <button onclick="openPasteModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                📋 Excel'den Yapıştır
            </button>
        </div>

        @if(session('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        <!-- Fiyat Listesi -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tedarikçi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Birim Fiyat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Para Birimi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min. Sipariş</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($fiyatlar as $fiyat)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $fiyat->tedarikci->sirket ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $fiyat->urun_adi }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $fiyat->tarih->format('d.m.Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold">
                                {{ number_format($fiyat->birim_fiyat, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $fiyat->para_birimi }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $fiyat->minimum_siparis }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button onclick="deleteFiyat({{ $fiyat->id }})" class="text-red-600 hover:text-red-900">Sil</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                Henüz fiyat eklenmemiş. Excel'den yapıştır butonunu kullanın.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paste Modal -->
    <div id="pasteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-6xl shadow-lg rounded-md bg-white mb-10">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Excel'den Toplu Fiyat Ekle</h3>
                <button onclick="closePasteModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Tedarikçi Seçimi -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tedarikçi *</label>
                <select id="tedarikciSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tedarikçi seçin...</option>
                    @foreach($tedarikciler as $tedarikci)
                        <option value="{{ $tedarikci->id }}">{{ $tedarikci->sirket }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Paste Area -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Excel Verisi</label>
                <div id="pasteArea" 
                    contenteditable="true" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                </div>
            </div>

            <div class="flex gap-3 mb-4">
                <button onclick="parseData()" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                    ✓ Verileri İşle
                </button>
                <button onclick="clearPasteArea()" class="bg-gray-400 hover:bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg">
                    Temizle
                </button>
            </div>

            <!-- Preview Table -->
            <div id="previewSection" class="hidden">
                <h4 class="text-md font-semibold mb-2">Önizleme ve Düzenleme</h4>
                <div class="overflow-x-auto mb-4">
                    <table class="min-w-full divide-y divide-gray-200 border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Ürün Adı</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Birim Fiyat</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Adet</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Para Birimi</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Marka</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">İşlem</th>
                            </tr>
                        </thead>
                        <tbody id="previewTableBody" class="bg-white divide-y divide-gray-200">
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end gap-3">
                    <button onclick="closePasteModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        İptal
                    </button>
                    <button onclick="saveAll()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        💾 Tümünü Kaydet
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let parsedData = [];
        const markalar = @json($markalar);

        $(document).ready(function() {
            $('#tedarikciSelect').select2({
                placeholder: 'Tedarikçi seçin...',
                language: {
                    noResults: function() { return "Sonuç bulunamadı"; },
                    searching: function() { return "Aranıyor..."; }
                }
            });
        });

        function openPasteModal() {
            document.getElementById('pasteModal').classList.remove('hidden');
            document.getElementById('pasteArea').focus();
        }

        function closePasteModal() {
            document.getElementById('pasteModal').classList.add('hidden');
            document.getElementById('pasteArea').innerHTML = '';
            document.getElementById('previewSection').classList.add('hidden');
            $('#tedarikciSelect').val('').trigger('change');
            parsedData = [];
        }

        function clearPasteArea() {
            document.getElementById('pasteArea').innerHTML = '';
            document.getElementById('previewSection').classList.add('hidden');
            parsedData = [];
        }

        function parseData() {
            const tedarikciId = $('#tedarikciSelect').val();
            if (!tedarikciId) {
                alert('Lütfen önce tedarikçi seçin!');
                return;
            }

            const pasteArea = document.getElementById('pasteArea');
            const text = pasteArea.innerText.trim();
            
            if (!text) {
                alert('Lütfen veri yapıştırın!');
                return;
            }

            const lines = text.split('\n').filter(line => line.trim());
            parsedData = [];

            lines.forEach((line, index) => {
                // Tab veya çoklu boşlukla ayrılmış değerleri parse et
                const parts = line.split(/\t+|\s{2,}/).map(p => p.trim()).filter(p => p);
                
                if (parts.length >= 2) {
                    const urunAdi = parts[0];
                    const birimFiyat = parseFloat(parts[1].replace(/[^0-9.,]/g, '').replace(',', '.'));
                    const adet = parts[2] ? parseInt(parts[2]) : 1;
                    const paraBirimi = parts[3] || 'TL';

                    if (urunAdi && !isNaN(birimFiyat)) {
                        parsedData.push({
                            urun_adi: urunAdi,
                            birim_fiyat: birimFiyat,
                            adet: adet,
                            para_birimi: paraBirimi,
                            marka_id: null
                        });
                    }
                }
            });

            if (parsedData.length === 0) {
                alert('Geçerli veri bulunamadı. Format: Ürün Adı [Tab] Birim Fiyat [Tab] Adet [Tab] Para Birimi');
                return;
            }

            renderPreviewTable();
            document.getElementById('previewSection').classList.remove('hidden');
        }

        function renderPreviewTable() {
            const tbody = document.getElementById('previewTableBody');
            tbody.innerHTML = '';

            parsedData.forEach((item, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-4 py-2 editable-cell" data-index="${index}" data-field="urun_adi">${item.urun_adi}</td>
                    <td class="px-4 py-2 editable-cell" data-index="${index}" data-field="birim_fiyat">${item.birim_fiyat}</td>
                    <td class="px-4 py-2 editable-cell" data-index="${index}" data-field="adet">${item.adet}</td>
                    <td class="px-4 py-2 editable-cell" data-index="${index}" data-field="para_birimi">${item.para_birimi}</td>
                    <td class="px-4 py-2">
                        <select class="marka-select border border-gray-300 rounded px-2 py-1" data-index="${index}">
                            <option value="">Marka seç...</option>
                            ${markalar.map(m => `<option value="${m.id}">${m.name || m.marka_adi || '-'}</option>`).join('')}
                        </select>
                    </td>
                    <td class="px-4 py-2">
                        <button onclick="removeRow(${index})" class="text-red-600 hover:text-red-800">Sil</button>
                    </td>
                `;
                tbody.appendChild(row);
            });

            // Marka değişikliklerini dinle
            document.querySelectorAll('.marka-select').forEach(select => {
                select.addEventListener('change', function() {
                    const index = parseInt(this.dataset.index);
                    parsedData[index].marka_id = this.value || null;
                });
            });

            // Inline editing
            document.querySelectorAll('.editable-cell').forEach(cell => {
                cell.addEventListener('click', function() {
                    if (this.classList.contains('editing')) return;

                    const index = parseInt(this.dataset.index);
                    const field = this.dataset.field;
                    const currentValue = this.textContent.trim();
                    const originalContent = this.innerHTML;

                    this.classList.add('editing');
                    this.innerHTML = `<input type="text" class="w-full px-2 py-1 border border-blue-500 rounded" value="${currentValue}">`;
                    
                    const input = this.querySelector('input');
                    input.focus();
                    input.select();

                    const saveEdit = () => {
                        const newValue = input.value.trim();
                        if (field === 'birim_fiyat') {
                            parsedData[index][field] = parseFloat(newValue);
                        } else if (field === 'adet') {
                            parsedData[index][field] = parseInt(newValue);
                        } else {
                            parsedData[index][field] = newValue;
                        }
                        this.classList.remove('editing');
                        this.textContent = newValue;
                    };

                    input.addEventListener('blur', saveEdit);
                    input.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            saveEdit();
                        } else if (e.key === 'Escape') {
                            this.classList.remove('editing');
                            this.innerHTML = originalContent;
                        }
                    });
                });
            });
        }

        function removeRow(index) {
            parsedData.splice(index, 1);
            renderPreviewTable();
        }

        function saveAll() {
            const tedarikciId = $('#tedarikciSelect').val();
            
            if (!tedarikciId) {
                alert('Tedarikçi seçilmemiş!');
                return;
            }

            if (parsedData.length === 0) {
                alert('Kaydedilecek veri yok!');
                return;
            }

            fetch('/tedarikci-fiyatlari/bulk', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    musteri_id: tedarikciId,
                    items: parsedData
                })
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

        function deleteFiyat(id) {
            if (!confirm('Bu fiyat kaydını silmek istediğinize emin misiniz?')) {
                return;
            }

            fetch(`/tedarikci-fiyatlari/${id}`, {
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
    </script>
</body>
</html>
