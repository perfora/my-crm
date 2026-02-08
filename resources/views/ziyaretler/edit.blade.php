<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ziyaret Düzenle - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-4">
            <a href="/ziyaretler" class="text-blue-600 hover:underline">← Geri</a>
        </div>
        
        <h1 class="text-3xl font-bold mb-6">Ziyaret Düzenle</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="/ziyaretler/{{ $ziyaret->id }}" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Ziyaret İsmi *</label>
                        <input type="text" name="ziyaret_ismi" value="{{ $ziyaret->ziyaret_ismi }}" required class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Müşteri</label>
                        <select name="musteri_id" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            @php
                                $musteriler = \App\Models\Musteri::orderBy('sirket')->get();
                            @endphp
                            @foreach($musteriler as $musteri)
                                <option value="{{ $musteri->id }}" {{ $ziyaret->musteri_id == $musteri->id ? 'selected' : '' }}>
                                    {{ $musteri->sirket }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Ziyaret Tarihi</label>
                        <input type="datetime-local" name="ziyaret_tarihi" 
                               value="{{ $ziyaret->ziyaret_tarihi ? $ziyaret->ziyaret_tarihi->format('Y-m-d\TH:i') : '' }}" 
                               class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Arama Tarihi</label>
                        <input type="datetime-local" name="arama_tarihi" 
                               value="{{ $ziyaret->arama_tarihi ? $ziyaret->arama_tarihi->format('Y-m-d\TH:i') : '' }}" 
                               class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Tür</label>
                        <select name="tur" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="Ziyaret" {{ $ziyaret->tur == 'Ziyaret' ? 'selected' : '' }}>Ziyaret</option>
                            <option value="Telefon" {{ $ziyaret->tur == 'Telefon' ? 'selected' : '' }}>Telefon</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Durumu</label>
                        <select name="durumu" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="Beklemede" {{ $ziyaret->durumu == 'Beklemede' ? 'selected' : '' }}>Beklemede</option>
                            <option value="Planlandı" {{ $ziyaret->durumu == 'Planlandı' ? 'selected' : '' }}>Planlandı</option>
                            <option value="Tamamlandı" {{ $ziyaret->durumu == 'Tamamlandı' ? 'selected' : '' }}>Tamamlandı</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Ziyaret Notları</label>
                    <textarea name="ziyaret_notlari" rows="4" class="w-full border rounded px-3 py-2">{{ $ziyaret->ziyaret_notlari }}</textarea>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                        Güncelle
                    </button>
                    <a href="/ziyaretler" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                        İptal
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
