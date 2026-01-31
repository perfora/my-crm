<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $musteri->sirket }} - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-4 flex justify-between items-center">
            <a href="/musteriler" class="text-blue-600 hover:underline">← Geri</a>
            <a href="/musteriler/{{ $musteri->id }}/edit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                ✏️ Düzenle
            </a>
        </div>
        
        <h1 class="text-3xl font-bold mb-6">{{ $musteri->sirket }}</h1>

        <!-- Müşteri Bilgileri Kartı -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-sm text-gray-600 mb-1">Şehir</div>
                    <div class="font-semibold text-lg">{{ $musteri->sehir ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Telefon</div>
                    <div class="font-semibold text-lg">
                        @if($musteri->telefon)
                            <a href="tel:{{ $musteri->telefon }}" class="text-blue-600 hover:underline">{{ $musteri->telefon }}</a>
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Derece</div>
                    <div class="font-semibold text-lg">{{ $musteri->derece ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Türü</div>
                    <div class="font-semibold text-lg">{{ $musteri->turu ?? '-' }}</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-sm text-gray-600 mb-1">Adres</div>
                    <div class="font-semibold text-base">{{ $musteri->adres ?? '-' }}</div>
                </div>
                @if($musteri->notlar)
                    <div class="md:col-span-2">
                        <div class="text-sm text-gray-600 mb-1">Notlar</div>
                        <div class="text-base whitespace-pre-wrap">{{ $musteri->notlar }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Toplam Bilgiler -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-gray-600 text-sm">İlişkili Kişiler</div>
                <div class="text-3xl font-bold text-blue-600">{{ $kisiler->count() }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-gray-600 text-sm">Ziyaretler</div>
                <div class="text-3xl font-bold text-green-600">{{ $ziyaretler->count() }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-gray-600 text-sm">Tüm İşler</div>
                <div class="text-3xl font-bold text-purple-600">{{ $isler->count() }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-gray-600 text-sm">Kazanıldı</div>
                <div class="text-2xl font-bold text-green-700">${{ number_format($kazanilanTotal, 2) }}</div>
            </div>
        </div>

        <!-- İlişkili Kişiler -->
        @if($kisiler->count() > 0)
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">İlişkili Kişiler ({{ $kisiler->count() }})</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($kisiler as $kisi)
                        <div class="border rounded-lg p-4 bg-gray-50 hover:bg-gray-100 transition">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="font-bold text-lg">{{ $kisi->ad_soyad }}</div>
                                    @if($kisi->gorev)
                                        <div class="text-sm text-gray-600">{{ $kisi->gorev }}</div>
                                    @endif
                                    @if($kisi->bolum)
                                        <div class="text-sm text-gray-600">{{ $kisi->bolum }}</div>
                                    @endif
                                </div>
                                <a href="/kisiler/{{ $kisi->id }}/edit" class="text-blue-600 hover:text-blue-800 text-sm">
                                    ✏️
                                </a>
                            </div>
                            <div class="mt-2 space-y-1 text-sm">
                                @if($kisi->telefon_numarasi)
                                    <div>📱 <a href="tel:{{ $kisi->telefon_numarasi }}" class="text-blue-600 hover:underline">{{ $kisi->telefon_numarasi }}</a></div>
                                @endif
                                @if($kisi->email_adresi)
                                    <div>📧 <a href="mailto:{{ $kisi->email_adresi }}" class="text-blue-600 hover:underline">{{ $kisi->email_adresi }}</a></div>
                                @endif
                                @if($kisi->url)
                                    <div>🔗 <a href="{{ $kisi->url }}" target="_blank" class="text-blue-600 hover:underline">Profil</a></div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Ziyaretler -->
        @if($ziyaretler->count() > 0)
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">Son Ziyaretler ({{ $ziyaretler->count() }})</h2>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($ziyaretler as $ziyaret)
                        <div class="border-l-4 border-green-400 pl-4 py-2">
                            <div class="font-semibold">{{ $ziyaret->ziyaret_ismi }}</div>
                            <div class="text-sm text-gray-600">
                                {{ $ziyaret->ziyaret_tarihi ? $ziyaret->ziyaret_tarihi->format('d.m.Y H:i') : 'Tarih belirtilmedi' }}
                                @if($ziyaret->tur)
                                    • <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">{{ $ziyaret->tur }}</span>
                                @endif
                                @if($ziyaret->durumu)
                                    • <span class="px-2 py-1 text-xs rounded 
                                        @if($ziyaret->durumu == 'Tamamlandı') bg-green-100 text-green-800
                                        @elseif($ziyaret->durumu == 'Planlandı') bg-blue-100 text-blue-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">{{ $ziyaret->durumu }}</span>
                                @endif
                            </div>
                            @if($ziyaret->ziyaret_notlari)
                                <div class="text-sm text-gray-700 mt-1 line-clamp-2">{{ $ziyaret->ziyaret_notlari }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Tüm İşler -->
        @if($isler->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-4">Tüm İşler ({{ $isler->count() }})</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">İş Adı</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Tipi</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Durum</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Teklif</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Alış</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Kar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($isler as $is)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-semibold">{{ $is->name }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ $is->tipi ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $is->durum ?? '-' }}</td>
                                    <td class="px-4 py-2">
                                        @if($is->teklif_doviz === 'USD')
                                            ${{ number_format($is->teklif_tutari ?? 0, 2) }}
                                        @else
                                            {{ number_format($is->teklif_tutari ?? 0, 2) }} TL
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($is->alis_doviz === 'USD')
                                            ${{ number_format($is->alis_tutari ?? 0, 2) }}
                                        @else
                                            {{ number_format($is->alis_tutari ?? 0, 2) }} TL
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 font-semibold {{ ($is->kar_tutari ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        @if($is->teklif_doviz === 'USD' || $is->alis_doviz === 'USD')
                                            ${{ number_format($is->kar_tutari ?? 0, 2) }}
                                        @else
                                            {{ number_format($is->kar_tutari ?? 0, 2) }} TL
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
