<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Yeni İş - Mobil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        body { -webkit-tap-highlight-color: transparent; }
        input, select, textarea { font-size: 16px !important; }
        .form-input { min-height: 50px; }
        
        /* Select2 mobil optimizasyonu */
        .select2-container--default .select2-selection--single {
            height: 50px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            padding: 0 1rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 50px !important;
            padding-left: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 48px !important;
            right: 8px !important;
        }
        .select2-dropdown {
            border-radius: 0.5rem !important;
            border: 1px solid #d1d5db !important;
        }
        .select2-results {
            max-height: 300px !important;
        }
        .select2-search--dropdown .select2-search__field {
            min-height: 50px !important;
            font-size: 16px !important;
            padding: 0.75rem 1rem !important;
            border-radius: 0.5rem !important;
        }
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
                <select name="musteri_id" id="musteri-select" required 
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg">
                    <option value="">Seçiniz</option>
                    @foreach(\App\Models\Musteri::orderBy('sirket')->get() as $musteri)
                        <option value="{{ $musteri->id }}">{{ $musteri->sirket }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Marka -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Marka</label>
                <select name="marka_id" id="marka-select"
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg">
                    <option value="">Seçiniz</option>
                    @foreach(\App\Models\Marka::orderBy('name')->get() as $marka)
                        <option value="{{ $marka->id }}">{{ $marka->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tipi -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">İş Tipi</label>
                <select name="tipi" id="tipi-select"
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg">
                    <option value="">Seçiniz</option>
                    @foreach(\App\Models\IsTipi::orderBy('name')->get() as $tip)
                        <option value="{{ $tip->name }}" {{ $tip->name === 'Verilecek' ? 'selected' : '' }}>{{ $tip->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Öncelik -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Öncelik</label>
                <div class="grid grid-cols-4 gap-2">
                    @foreach(\App\Models\Oncelik::orderBy('sira')->get() as $oncelik)
                        <label class="relative">
                            <input type="radio" name="oncelik" value="{{ $oncelik->name }}" class="hidden peer" {{ (string)$oncelik->name === '1' ? 'checked' : '' }}>
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
    
    <script>
        $(document).ready(function() {
            // Select2 başlat - müşteri, marka ve iş tipi için
            $('#musteri-select, #marka-select, #tipi-select').select2({
                placeholder: 'Seçiniz veya arayın...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return 'Sonuç bulunamadı';
                    },
                    searching: function() {
                        return 'Aranıyor...';
                    },
                    inputTooShort: function() {
                        return 'Aramak için yazın...';
                    }
                }
            });
        });
    </script>
</body>
</html>
