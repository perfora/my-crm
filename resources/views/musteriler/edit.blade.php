<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Düzenle - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-4">
            <a href="/musteriler/{{ $musteri->id }}" class="text-blue-600 hover:underline">← Geri</a>
        </div>
        
        <h1 class="text-3xl font-bold mb-6">{{ $musteri->sirket }} - Düzenle</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="/musteriler/{{ $musteri->id }}" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Şirket Adı *</label>
                        <input type="text" name="sirket" value="{{ $musteri->sirket }}" required class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Şehir</label>
                        <input type="text" name="sehir" value="{{ $musteri->sehir }}" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Telefon</label>
                        <input type="text" name="telefon" value="{{ $musteri->telefon }}" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Derece</label>
                        <select name="derece" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="1 -Sık" {{ $musteri->derece == '1 -Sık' ? 'selected' : '' }}>1 - Sık</option>
                            <option value="2 - Orta" {{ $musteri->derece == '2 - Orta' ? 'selected' : '' }}>2 - Orta</option>
                            <option value="3- Düşük" {{ $musteri->derece == '3- Düşük' ? 'selected' : '' }}>3 - Düşük</option>
                            <option value="4 - Potansiyel" {{ $musteri->derece == '4 - Potansiyel' ? 'selected' : '' }}>4 - Potansiyel</option>
                            <option value="5 - İş Ortağı" {{ $musteri->derece == '5 - İş Ortağı' ? 'selected' : '' }}>5 - İş Ortağı</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Türü</label>
                        <select name="turu" class="w-full border rounded px-3 py-2">
                            <option value="">Seçiniz</option>
                            <option value="Netcom" {{ $musteri->turu == 'Netcom' ? 'selected' : '' }}>Netcom</option>
                            <option value="Bayi" {{ $musteri->turu == 'Bayi' ? 'selected' : '' }}>Bayi</option>
                            <option value="Resmi Kurum" {{ $musteri->turu == 'Resmi Kurum' ? 'selected' : '' }}>Resmi Kurum</option>
                            <option value="Üniversite" {{ $musteri->turu == 'Üniversite' ? 'selected' : '' }}>Üniversite</option>
                            <option value="Belediye" {{ $musteri->turu == 'Belediye' ? 'selected' : '' }}>Belediye</option>
                            <option value="Hastane" {{ $musteri->turu == 'Hastane' ? 'selected' : '' }}>Hastane</option>
                            <option value="Özel Sektör" {{ $musteri->turu == 'Özel Sektör' ? 'selected' : '' }}>Özel Sektör</option>
                            <option value="Tedarikçi" {{ $musteri->turu == 'Tedarikçi' ? 'selected' : '' }}>Tedarikçi</option>
                            <option value="Üretici" {{ $musteri->turu == 'Üretici' ? 'selected' : '' }}>Üretici</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Adres</label>
                    <textarea name="adres" rows="2" class="w-full border rounded px-3 py-2">{{ $musteri->adres }}</textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Notlar</label>
                    <textarea name="notlar" rows="3" class="w-full border rounded px-3 py-2">{{ $musteri->notlar }}</textarea>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                        Güncelle
                    </button>
                    <a href="/musteriler/{{ $musteri->id }}" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                        İptal
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
