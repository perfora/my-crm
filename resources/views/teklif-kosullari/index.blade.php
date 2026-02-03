<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teklif Koşulları Yönetimi - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/lang/summernote-tr-TR.min.js"></script>
</head>
<body class="bg-gray-50">
    @include('layouts.nav')

    <div class="container mx-auto px-4 py-6 max-w-7xl">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">📋 Teklif Koşulları Yönetimi</h1>
            <button onclick="yeniKosulModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
                ➕ Yeni Koşul Ekle
            </button>
        </div>

        <!-- Liste -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Başlık</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İçerik Önizleme</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Varsayılan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sıra</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($kosullar as $kosul)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">{{ $kosul->baslik }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 line-clamp-2">
                                {!! Str::limit(strip_tags($kosul->icerik), 100) !!}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($kosul->varsayilan)
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">✓ Varsayılan</span>
                            @else
                                <button onclick="varsayilanYap({{ $kosul->id }})" class="text-gray-400 hover:text-green-600 text-xs">
                                    Varsayılan Yap
                                </button>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600">{{ $kosul->sira }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button onclick="duzenleModal({{ $kosul->id }})" class="text-blue-600 hover:text-blue-900 mr-3">✏️ Düzenle</button>
                            <button onclick="sil({{ $kosul->id }})" class="text-red-600 hover:text-red-900">🗑️ Sil</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            Henüz teklif koşulu eklenmemiş. Yukarıdaki "Yeni Koşul Ekle" butonuna tıklayarak başlayabilirsiniz.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="kosulModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b">
                <h2 id="modalBaslik" class="text-2xl font-bold text-gray-800">Yeni Koşul Ekle</h2>
            </div>
            <form id="kosulForm" class="p-6 space-y-4">
                <input type="hidden" id="kosul_id" name="kosul_id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Başlık</label>
                    <input type="text" id="baslik" name="baslik" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Örn: Standart Koşullar, Lisans Satış Koşulları">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sıra</label>
                    <input type="number" id="sira" name="sira" value="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" id="varsayilan" name="varsayilan" class="rounded">
                        <span class="text-sm font-medium text-gray-700">Varsayılan olarak seçili gelsin</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        İçerik (Word'den kopyalayıp yapıştırabilirsiniz)
                    </label>
                    <textarea id="icerik" name="icerik" rows="15"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="modalKapat()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        İptal
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        💾 Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Summernote başlat
        $(document).ready(function() {
            $('#icerik').summernote({
                height: 400,
                lang: 'tr-TR',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                placeholder: 'Word\'den kopyalayıp yapıştırabilirsiniz...'
            });
        });

        function yeniKosulModal() {
            document.getElementById('modalBaslik').textContent = 'Yeni Koşul Ekle';
            document.getElementById('kosulForm').reset();
            document.getElementById('kosul_id').value = '';
            $('#icerik').summernote('code', '');
            document.getElementById('kosulModal').classList.remove('hidden');
        }

        function modalKapat() {
            document.getElementById('kosulModal').classList.add('hidden');
        }

        function duzenleModal(id) {
            fetch('/teklif-kosullari/' + id + '/edit')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('modalBaslik').textContent = 'Koşulu Düzenle';
                    document.getElementById('kosul_id').value = data.id;
                    document.getElementById('baslik').value = data.baslik;
                    document.getElementById('sira').value = data.sira;
                    document.getElementById('varsayilan').checked = data.varsayilan;
                    $('#icerik').summernote('code', data.icerik);
                    document.getElementById('kosulModal').classList.remove('hidden');
                });
        }

        function varsayilanYap(id) {
            if (!confirm('Bu koşulu varsayılan yapmak istediğinize emin misiniz?')) return;
            
            fetch('/teklif-kosullari/' + id + '/varsayilan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => location.reload());
        }

        function sil(id) {
            if (!confirm('Bu koşulu silmek istediğinize emin misiniz?')) return;
            
            fetch('/teklif-kosullari/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => location.reload());
        }

        // Form submit
        document.getElementById('kosulForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const id = document.getElementById('kosul_id').value;
            const url = id ? '/teklif-kosullari/' + id : '/teklif-kosullari';
            const method = id ? 'PUT' : 'POST';
            
            const formData = {
                baslik: document.getElementById('baslik').value,
                sira: document.getElementById('sira').value,
                varsayilan: document.getElementById('varsayilan').checked,
                icerik: $('#icerik').summernote('code')
            };
            
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            }).then(() => location.reload());
        });

        // Modal dışına tıklayınca kapat
        document.getElementById('kosulModal').addEventListener('click', function(e) {
            if (e.target === this) modalKapat();
        });
    </script>
</body>
</html>
