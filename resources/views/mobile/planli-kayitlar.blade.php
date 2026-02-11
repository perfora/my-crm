<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Planlı Kayıtlarım - Mobil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { -webkit-tap-highlight-color: transparent; }
        input, textarea { font-size: 16px !important; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <div class="bg-indigo-600 text-white p-4 shadow-lg flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/mobile" class="text-3xl">←</a>
                <h1 class="text-xl font-bold">Planlı Kayıtlarım</h1>
            </div>
            <a href="/mobile/hizli-kayit" class="text-xs bg-white text-indigo-700 px-3 py-2 rounded-lg font-semibold">+ Hızlı</a>
        </div>

        @if(session('message'))
            <div class="m-4 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        @php
            $planliKayitlar = \App\Models\Ziyaret::with('musteri')
                ->whereIn('durumu', ['Beklemede', 'Planlandı'])
                ->orderByRaw('COALESCE(ziyaret_tarihi, arama_tarihi) ASC')
                ->limit(80)
                ->get();
        @endphp

        <div class="p-4 space-y-4">
            @forelse($planliKayitlar as $kayit)
                @php
                    $planTarihi = $kayit->tur === 'Telefon' ? $kayit->arama_tarihi : $kayit->ziyaret_tarihi;
                    $isTelefon = mb_strtolower((string) $kayit->tur) === 'telefon';
                @endphp
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-bold text-gray-800">{{ $kayit->ziyaret_ismi ?: ($kayit->musteri->sirket ?? 'Kayıt') }}</div>
                            <div class="text-sm text-gray-600 mt-1">{{ $kayit->musteri->sirket ?? '-' }}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                Planlanan: {{ $planTarihi ? $planTarihi->timezone(config('crm.timezone'))->format(config('crm.datetime_format')) : '-' }}
                            </div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full {{ $isTelefon ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $kayit->tur ?: ($isTelefon ? 'Telefon' : 'Ziyaret') }}
                        </span>
                    </div>

                    <form action="/mobile/ziyaretler/{{ $kayit->id }}/tamamla" method="POST" class="mt-3 space-y-2">
                        @csrf
                        <textarea name="ziyaret_notlari" rows="3" class="w-full border border-gray-300 rounded-lg p-3" placeholder="Not ekle (opsiyonel)..."></textarea>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg">
                            Tamamla + Not
                        </button>
                    </form>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow p-6 text-center text-gray-500">
                    Beklemede veya planlı kayıt yok.
                </div>
            @endforelse
        </div>
    </div>
</body>
</html>
