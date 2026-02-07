<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Dışa Aktar - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    @include('layouts.nav')

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-2">Sistem Dışa Aktar</h1>
        <p class="text-sm text-gray-600 mb-6">Excel uyumlu CSV export. Birden çok seçimde ZIP olarak iner.</p>

        @if($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('system-export.download') }}">
                @csrf

                <div class="flex items-center gap-4 mb-4">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" id="select-all" class="h-4 w-4 rounded border-gray-300">
                        Tümünü Seç
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
                    @foreach($datasets as $key => $dataset)
                        <label class="flex items-center gap-2 rounded border border-gray-200 bg-gray-50 px-3 py-2 hover:bg-gray-100">
                            <input type="checkbox" name="datasets[]" value="{{ $key }}" class="dataset-checkbox h-4 w-4 rounded border-gray-300">
                            <span class="text-sm">{{ $dataset['label'] }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded">
                        Dışa Aktar
                    </button>
                    <span class="text-xs text-gray-500">Tek seçim: `.csv` | Çoklu seçim: `.zip`</span>
                </div>
            </form>
        </div>
    </div>

    <script>
        const selectAll = document.getElementById('select-all');
        const datasetCheckboxes = Array.from(document.querySelectorAll('.dataset-checkbox'));

        selectAll.addEventListener('change', function () {
            datasetCheckboxes.forEach(function (cb) {
                cb.checked = selectAll.checked;
            });
        });

        datasetCheckboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                selectAll.checked = datasetCheckboxes.every(function (x) { return x.checked; });
            });
        });
    </script>
</body>
</html>
