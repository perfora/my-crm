<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Yeni Ziyaret - Mobil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { -webkit-tap-highlight-color: transparent; }
        input, select, textarea { font-size: 16px !important; }
        .form-input { min-height: 50px; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-purple-600 text-white p-4 shadow-lg flex items-center gap-3">
            <a href="/mobile" class="text-3xl">←</a>
            <h1 class="text-xl font-bold">Yeni Ziyaret Ekle</h1>
        </div>

        <!-- Form -->
        <form action="/ziyaretler" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- Müşteri -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Müşteri/Firma *</label>
                <select name="musteri_id" required 
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Seçiniz</option>
                    @foreach(\App\Models\Musteri::orderBy('sirket')->get() as $musteri)
                        <option value="{{ $musteri->id }}">{{ $musteri->sirket }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Ziyaret Tarihi -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Ziyaret Tarihi *</label>
                <input type="date" name="ziyaret_tarihi" required value="{{ date('Y-m-d') }}"
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- Durum -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Durum *</label>
                <div class="space-y-2">
                    <label class="block">
                        <input type="radio" name="durumu" value="Tamamlandı" class="hidden peer" checked>
                        <div class="peer-checked:bg-purple-600 peer-checked:text-white bg-white border-2 border-gray-300 rounded-lg p-4 text-center font-semibold cursor-pointer active:scale-95 transition">
                            ✅ Tamamlandı
                        </div>
                    </label>
                    <label class="block">
                        <input type="radio" name="durumu" value="Planlandı" class="hidden peer">
                        <div class="peer-checked:bg-purple-600 peer-checked:text-white bg-white border-2 border-gray-300 rounded-lg p-4 text-center font-semibold cursor-pointer active:scale-95 transition">
                            📅 Planlandı
                        </div>
                    </label>
                    <label class="block">
                        <input type="radio" name="durumu" value="Beklemede" class="hidden peer">
                        <div class="peer-checked:bg-purple-600 peer-checked:text-white bg-white border-2 border-gray-300 rounded-lg p-4 text-center font-semibold cursor-pointer active:scale-95 transition">
                            ⏳ Beklemede
                        </div>
                    </label>
                </div>
            </div>

            <!-- Notlar -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Görüşme Notları</label>
                <textarea name="notlar" rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                    placeholder="Ziyarette konuşulanlar..."></textarea>
            </div>

            <!-- Kaydet Butonu -->
            <button type="submit" 
                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 rounded-lg shadow-lg active:scale-95 transition-transform text-lg">
                ✅ Kaydet
            </button>

            <a href="/mobile" 
                class="block w-full bg-gray-400 hover:bg-gray-500 text-white font-bold py-4 rounded-lg text-center">
                ❌ İptal
            </a>
        </form>
    </div>

    @if(session('message'))
        <div class="fixed bottom-4 left-4 right-4 bg-purple-500 text-white p-4 rounded-lg shadow-lg">
            {{ session('message') }}
        </div>
    @endif
</body>
</html>
