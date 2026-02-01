<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Yeni İş - Mobil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { -webkit-tap-highlight-color: transparent; }
        input, select, textarea { font-size: 16px !important; }
        .form-input { min-height: 50px; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-green-600 text-white p-4 shadow-lg flex items-center gap-3">
            <a href="/mobile" class="text-3xl">←</a>
            <h1 class="text-xl font-bold">Yeni İş Ekle</h1>
        </div>

        <!-- Form -->
        <form action="/tum-isler" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- İş Adı -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">İş Adı *</label>
                <input type="text" name="name" required 
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="İş adını giriniz">
            </div>

            <!-- Müşteri -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Müşteri/Firma *</label>
                <select name="musteri_id" required 
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Seçiniz</option>
                    @foreach(\App\Models\Musteri::orderBy('sirket')->get() as $musteri)
                        <option value="{{ $musteri->id }}">{{ $musteri->sirket }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Marka -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Marka</label>
                <select name="marka_id" 
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Seçiniz</option>
                    @foreach(\App\Models\Marka::orderBy('name')->get() as $marka)
                        <option value="{{ $marka->id }}">{{ $marka->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tipi -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">İş Tipi</label>
                <select name="tipi" 
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Seçiniz</option>
                    @foreach(\App\Models\IsTipi::orderBy('name')->get() as $tip)
                        <option value="{{ $tip->name }}">{{ $tip->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Öncelik -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Öncelik</label>
                <div class="grid grid-cols-4 gap-2">
                    @foreach(\App\Models\Oncelik::orderBy('sira')->get() as $oncelik)
                        <label class="relative">
                            <input type="radio" name="oncelik" value="{{ $oncelik->name }}" class="hidden peer">
                            <div class="peer-checked:bg-green-600 peer-checked:text-white bg-white border-2 border-gray-300 rounded-lg p-4 text-center font-bold text-lg cursor-pointer active:scale-95 transition">
                                {{ $oncelik->name }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Teklif Tutarı -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Teklif Tutarı</label>
                <input type="number" name="teklif_tutari" step="0.01"
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                    placeholder="0.00">
            </div>

            <!-- Notlar -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Notlar</label>
                <textarea name="notlar" rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                    placeholder="İşle ilgili notlar..."></textarea>
            </div>

            <!-- Kaydet Butonu -->
            <button type="submit" 
                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-lg shadow-lg active:scale-95 transition-transform text-lg">
                ✅ Kaydet
            </button>

            <a href="/mobile" 
                class="block w-full bg-gray-400 hover:bg-gray-500 text-white font-bold py-4 rounded-lg text-center">
                ❌ İptal
            </a>
        </form>
    </div>

    @if(session('message'))
        <div class="fixed bottom-4 left-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg animate-bounce">
            {{ session('message') }}
        </div>
    @endif
</body>
</html>
