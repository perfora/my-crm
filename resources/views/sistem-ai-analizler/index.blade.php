<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Analizler - CRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    @include('layouts.nav')

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">AI Analizler</h1>
                <p class="mt-2 text-sm text-gray-600">CRM verisini OpenAI ile yorumlar, sonucu kaydeder ve tekrar incelemenizi saglar.</p>
            </div>
            <div class="text-sm text-gray-500">
                Son {{ $analyses->count() }} kayit
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            <div class="xl:col-span-2 bg-white rounded-lg shadow-lg border-t-4 border-blue-500 p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Hazir Analizler</h2>
                        <p class="text-sm text-gray-500 mt-1">Ilk fazda iki analiz tipi aktif. Sonraki fazda musteri, yenileme ve tum isler analizleri eklenebilir.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <form method="POST" action="{{ route('system.ai-analyses.store') }}" class="rounded-lg border border-blue-100 bg-blue-50/50 p-4">
                        @csrf
                        <input type="hidden" name="analysis_type" value="dashboard">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">Dashboard Analizi</h3>
                                <p class="text-sm text-gray-600 mt-1">Musteri, is, ziyaret, teklif, alis ve kar toplamlarini yonetici ozetine cevirir.</p>
                            </div>
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">Dashboard</span>
                        </div>
                        <button type="submit" class="mt-4 inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            Analizi Calistir
                        </button>
                    </form>

                    <form method="POST" action="{{ route('system.ai-analyses.store') }}" class="rounded-lg border border-purple-100 bg-purple-50/50 p-4">
                        @csrf
                        <input type="hidden" name="analysis_type" value="ziyaret">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">Ziyaret Analizi</h3>
                                <p class="text-sm text-gray-600 mt-1">Ziyaret durumlari, tur dagilimi ve son kayitlari operasyonel yorum haline getirir.</p>
                            </div>
                            <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-700">Ziyaret</span>
                        </div>
                        <button type="submit" class="mt-4 inline-flex items-center justify-center rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
                            Analizi Calistir
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg border-t-4 border-gray-400 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtreler</h2>
                <form method="GET" action="{{ route('system.ai-analyses.index') }}" class="space-y-3">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Analiz Tipi</label>
                        <select id="type" name="type" class="w-full border rounded px-3 py-2">
                            <option value="">Tum tipler</option>
                            @foreach ($analysisTypes as $value => $label)
                                <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
                        <select id="status" name="status" class="w-full border rounded px-3 py-2">
                            <option value="">Tum durumlar</option>
                            <option value="completed" @selected(request('status') === 'completed')>Tamamlandi</option>
                            <option value="failed" @selected(request('status') === 'failed')>Hata</option>
                            <option value="pending" @selected(request('status') === 'pending')>Bekliyor</option>
                        </select>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Filtrele</button>
                        <a href="{{ route('system.ai-analyses.index') }}" class="flex-1 bg-gray-200 hover:bg-gray-300 text-center text-gray-700 px-4 py-2 rounded">Temizle</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-5">
            @forelse ($analyses as $analysis)
                <section class="bg-white rounded-lg shadow-lg overflow-hidden {{ (string) request('highlight') === (string) $analysis->id ? 'ring-2 ring-blue-500' : '' }}">
                    <div class="border-b bg-gray-50 px-5 py-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $analysis->title }}</h3>
                            <div class="mt-1 text-xs text-gray-500">
                                {{ $analysis->created_at?->format('d.m.Y H:i:s') }}
                                · {{ $analysis->type_label }}
                                · {{ $analysis->user?->name ?? 'Sistem' }}
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium
                                {{ $analysis->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $analysis->status === 'failed' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $analysis->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                {{ $analysis->status_label }}
                            </span>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">Prompt v{{ $analysis->prompt_version }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 2xl:grid-cols-3">
                        <div class="2xl:col-span-2 p-5 border-b 2xl:border-b-0 2xl:border-r bg-white">
                            @if ($analysis->response_text)
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 whitespace-pre-wrap text-sm leading-7 text-gray-800">{{ $analysis->response_text }}</div>
                            @elseif ($analysis->error_message)
                                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                                    <div class="font-medium mb-2">Analiz hatayla sonlandi.</div>
                                    <div>{{ \Illuminate\Support\Str::limit($analysis->error_message, 180) }}</div>
                                    @if (\Illuminate\Support\Str::length($analysis->error_message) > 180)
                                        <details class="mt-3">
                                            <summary class="cursor-pointer text-xs font-medium text-red-800">Hata detayini goster</summary>
                                            <div class="mt-2 whitespace-pre-wrap rounded border border-red-200 bg-white p-3 text-xs leading-5 text-red-700">{{ $analysis->error_message }}</div>
                                        </details>
                                    @endif
                                </div>
                            @else
                                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-700">Sonuc bekleniyor.</div>
                            @endif
                        </div>

                        <aside class="p-5 bg-gray-50">
                            <details class="rounded-lg border border-gray-200 bg-white">
                                <summary class="cursor-pointer list-none px-3 py-3 text-sm font-semibold text-gray-900">
                                    Analiz Detayi
                                </summary>
                                <div class="border-t border-gray-200 p-3">
                                    <dl class="grid grid-cols-1 gap-3 text-sm">
                                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                            <dt class="text-xs uppercase tracking-wide text-gray-500">Kaynak Sayfa</dt>
                                            <dd class="mt-1 text-gray-900">{{ $analysis->source_page ?? '-' }}</dd>
                                        </div>
                                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                            <dt class="text-xs uppercase tracking-wide text-gray-500">Prompt Anahtari</dt>
                                            <dd class="mt-1 text-gray-900">{{ $analysis->prompt_key }}</dd>
                                        </div>
                                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                            <dt class="text-xs uppercase tracking-wide text-gray-500">Model</dt>
                                            <dd class="mt-1 text-gray-900">{{ data_get($analysis->response_meta, 'model', '-') }}</dd>
                                        </div>
                                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                            <dt class="text-xs uppercase tracking-wide text-gray-500">Response ID</dt>
                                            <dd class="mt-1 break-all text-gray-900">{{ data_get($analysis->response_meta, 'response_id', '-') }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </details>

                            <details class="mt-4 rounded-lg border border-gray-200 bg-white">
                                <summary class="cursor-pointer list-none px-3 py-3 text-sm font-semibold text-gray-900">
                                    Giren Veri
                                </summary>
                                <div class="border-t border-gray-200 p-3">
                                    <pre class="max-h-80 overflow-auto text-xs leading-5 text-gray-700">{{ json_encode($analysis->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            </details>
                        </aside>
                    </div>
                </section>
            @empty
                <div class="bg-white rounded-lg shadow-lg p-10 text-center text-gray-500">
                    Henuz AI analiz kaydi yok.
                </div>
            @endforelse
        </div>
    </div>
</body>
</html>
