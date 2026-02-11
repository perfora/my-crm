<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Hızlı Kayıt - Mobil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        body { -webkit-tap-highlight-color: transparent; }
        input, select, textarea { font-size: 16px !important; }
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
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <div class="bg-emerald-600 text-white p-4 shadow-lg flex items-center gap-3">
            <a href="/mobile" class="text-3xl">←</a>
            <h1 class="text-xl font-bold">Hızlı Kayıt</h1>
        </div>

        @if(session('message'))
            <div class="m-4 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        <form action="/mobile/hizli-kayit" method="POST" class="p-4 space-y-4">
            @csrf
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Müşteri *</label>
                <select name="musteri_id" id="musteri-select" required class="w-full border border-gray-300 rounded-lg">
                    <option value="">Seçiniz</option>
                    @foreach(\App\Models\Musteri::orderBy('sirket')->get() as $m)
                        <option value="{{ $m->id }}">{{ $m->sirket }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">Temas Türü *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block">
                        <input type="radio" name="contact_type" value="Telefon" class="hidden peer" required checked>
                        <div class="peer-checked:bg-emerald-600 peer-checked:text-white bg-white border-2 border-gray-300 rounded-lg p-4 text-center font-semibold cursor-pointer">
                            📞 Arama
                        </div>
                    </label>
                    <label class="block">
                        <input type="radio" name="contact_type" value="Ziyaret" class="hidden peer" required>
                        <div class="peer-checked:bg-emerald-600 peer-checked:text-white bg-white border-2 border-gray-300 rounded-lg p-4 text-center font-semibold cursor-pointer">
                            👥 Ziyaret
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">Not (opsiyonel)</label>
                <textarea name="ziyaret_notlari" rows="4" class="w-full border border-gray-300 rounded-lg p-3" placeholder="Not ekleyebilirsin..."></textarea>
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-lg shadow-lg">
                Kaydet
            </button>
            <a href="/mobile/planli-kayitlar" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-lg text-center">
                Planlı Kayıtları Aç
            </a>
        </form>
    </div>

    <script>
        $(function() {
            $('#musteri-select').select2({
                placeholder: 'Seçiniz veya arayın...',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
</body>
</html>

