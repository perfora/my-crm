<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kişi Düzenle - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-4">
            <a href="/kisiler" class="text-blue-600 hover:underline">← Geri</a>
        </div>
        
        <h1 class="text-3xl font-bold mb-6">Kişi Düzenle</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="/kisiler/{{ $kisi->id }}" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Ad Soyad *</label>
                        <input type="text" name="ad_soyad" value="{{ $kisi->ad_soyad }}" required class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Firma</label>
                        <select name="musteri_id" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            @php
                                $musteriler = \App\Models\Musteri::orderBy('sirket')->get();
                            @endphp
                            @foreach($musteriler as $musteri)
                                <option value="{{ $musteri->id }}" {{ $kisi->musteri_id == $musteri->id ? 'selected' : '' }}>
                                    {{ $musteri->sirket }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Telefon</label>
                        <input type="text" name="telefon_numarasi" value="{{ $kisi->telefon_numarasi }}" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="email_adresi" value="{{ $kisi->email_adresi }}" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Bölüm</label>
                        <input type="text" name="bolum" value="{{ $kisi->bolum }}" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Görev</label>
                        <input type="text" name="gorev" value="{{ $kisi->gorev }}" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">URL</label>
                        <input type="url" name="url" value="{{ $kisi->url }}" class="w-full border rounded px-3 py-2" placeholder="https://...">
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                        Güncelle
                    </button>
                    <a href="/kisiler" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                        İptal
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>