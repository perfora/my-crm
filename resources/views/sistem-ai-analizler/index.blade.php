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
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold">AI Analizler</h1>
                <p class="text-sm text-gray-600 mt-1">CRM verisini OpenAI ile yorumlar ve gecmisi saklar.</p>
            </div>
            <span class="text-sm text-gray-600">Son {{ $analyses->count() }} kayit</span>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-1 bg-white rounded-lg shadow p-4">
                <h2 class="text-lg font-semibold mb-4">Yeni Analiz</h2>

                <form method="POST" action="{{ route('system.ai-analyses.store') }}" class="space-y-3">
                    @csrf

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Analiz Tipi</span>
                        <select name="analysis_type" class="mt-1 w-full border rounded px-3 py-2">
                            @foreach ($analysisTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Analiz Olustur
                    </button>
                </form>

                <div class="mt-4 text-xs text-gray-500 space-y-1">
                    <p>Ilk fazda desteklenen analizler:</p>
                    <p>- Dashboard Analizi</p>
                    <p>- Ziyaret Analizi</p>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-lg shadow p-4">
                <h2 class="text-lg font-semibold mb-4">Filtreler</h2>
                <form method="GET" action="{{ route('system.ai-analyses.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <select name="type" class="border rounded px-3 py-2">
                        <option value="">Tum tipler</option>
                        @foreach ($analysisTypes as $value => $label)
                            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="border rounded px-3 py-2">
                        <option value="">Tum durumlar</option>
                        <option value="completed" @selected(request('status') === 'completed')>Tamamlandi</option>
                        <option value="failed" @selected(request('status') === 'failed')>Hata</option>
                        <option value="pending" @selected(request('status') === 'pending')>Bekliyor</option>
                    </select>
                    <div class="flex gap-2 md:col-span-2">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Filtrele</button>
                        <a href="{{ route('system.ai-analyses.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">Temizle</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($analyses as $analysis)
                <div class="bg-white rounded-lg shadow overflow-hidden {{ (string) request('highlight') === (string) $analysis->id ? 'ring-2 ring-blue-500' : '' }}">
                    <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between gap-4">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $analysis->title }}</div>
                            <div class="text-xs text-gray-500">
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
                            <span class="text-xs text-gray-500">Prompt v{{ $analysis->prompt_version }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-3">
                        <div class="xl:col-span-2 p-4 border-b xl:border-b-0 xl:border-r">
                            @if ($analysis->response_text)
                                <div class="whitespace-pre-wrap text-sm leading-6 text-gray-800">{{ $analysis->response_text }}</div>
                            @elseif ($analysis->error_message)
                                <div class="text-sm text-red-700 whitespace-pre-wrap">{{ $analysis->error_message }}</div>
                            @else
                                <div class="text-sm text-gray-500">Sonuc bekleniyor.</div>
                            @endif
                        </div>
                        <div class="p-4 bg-gray-50">
                            <h3 class="text-sm font-semibold mb-3">Kayit Detayi</h3>
                            <dl class="space-y-2 text-sm">
                                <div>
                                    <dt class="text-gray-500">Kaynak Sayfa</dt>
                                    <dd class="text-gray-900">{{ $analysis->source_page ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Prompt Anahtari</dt>
                                    <dd class="text-gray-900">{{ $analysis->prompt_key }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Model</dt>
                                    <dd class="text-gray-900">{{ data_get($analysis->response_meta, 'model', '-') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Response ID</dt>
                                    <dd class="text-gray-900 break-all">{{ data_get($analysis->response_meta, 'response_id', '-') }}</dd>
                                </div>
                            </dl>

                            <div class="mt-4">
                                <h4 class="text-sm font-semibold mb-2">Giren Veri</h4>
                                <pre class="bg-white border rounded p-3 text-xs overflow-auto max-h-72">{{ json_encode($analysis->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                    Henuz AI analiz kaydi yok.
                </div>
            @endforelse
        </div>
    </div>
</body>
</html>
