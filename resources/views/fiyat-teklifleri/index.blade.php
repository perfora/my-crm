<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiyat Teklifleri - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-50">
    @include('layouts.nav')

    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Fiyat Teklifleri</h1>
            <a href="/fiyat-teklifleri/yeni" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                + Yeni Teklif
            </a>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teklif No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Toplam Satış</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($teklifler as $teklif)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-blue-600">
                                <a href="/fiyat-teklifleri/{{ $teklif->id }}" class="hover:underline">
                                    {{ $teklif->teklif_no }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $teklif->musteri->sirket ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($teklif->tarih)->format('d.m.Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold">
                                {{ number_format($teklif->toplam_satis, 2) }} {{ $teklif->para_birimi ?? 'TL' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-green-600">
                                {{ number_format($teklif->toplam_kar, 2) }} {{ $teklif->para_birimi ?? 'TL' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $teklif->durum === 'Taslak' ? 'bg-gray-200 text-gray-800' : '' }}
                                    {{ $teklif->durum === 'Gönderildi' ? 'bg-blue-200 text-blue-800' : '' }}
                                    {{ $teklif->durum === 'Onaylandı' ? 'bg-green-200 text-green-800' : '' }}
                                    {{ $teklif->durum === 'Reddedildi' ? 'bg-red-200 text-red-800' : '' }}">
                                    {{ $teklif->durum }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="/fiyat-teklifleri/{{ $teklif->id }}" class="text-blue-600 hover:text-blue-900 mr-3">Görüntüle</a>
                                <button onclick="deleteTeklif({{ $teklif->id }})" class="text-red-600 hover:text-red-900">Sil</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                Henüz teklif oluşturulmamış.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function deleteTeklif(id) {
            if (!confirm('Bu teklifi silmek istediğinize emin misiniz?')) {
                return;
            }

            fetch(`/fiyat-teklifleri/${id}`, {
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
