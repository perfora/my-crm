<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marka Düzenle - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-4">
            <a href="/markalar" class="text-blue-600 hover:underline">← Geri</a>
        </div>
        
        <h1 class="text-3xl font-bold mb-6">Marka Düzenle</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="/markalar/{{ $marka->id }}" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-sm font-medium mb-1">Marka Adı *</label>
                    <input type="text" name="name" value="{{ $marka->name }}" required class="w-full border rounded px-3 py-2">
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                        Güncelle
                    </button>
                    <a href="/markalar" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                        İptal
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>