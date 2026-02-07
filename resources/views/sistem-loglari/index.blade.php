<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Logları - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    @include('layouts.nav')

    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold">Sistem Logları</h1>
            <span class="text-sm text-gray-600">Son {{ $logs->count() }} kayıt</span>
        </div>

        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form method="GET" action="/sistem-loglari" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="text" name="q" value="{{ request('q') }}" class="border rounded px-3 py-2" placeholder="Mesaj, url, kaynak ara...">
                <select name="channel" class="border rounded px-3 py-2">
                    <option value="">Tüm Kanallar</option>
                    <option value="server" @selected(request('channel') === 'server')>Server</option>
                    <option value="client" @selected(request('channel') === 'client')>Client</option>
                    <option value="journal" @selected(request('channel') === 'journal')>Journal</option>
                </select>
                <select name="level" class="border rounded px-3 py-2">
                    <option value="">Tüm Seviyeler</option>
                    <option value="error" @selected(request('level') === 'error')>Error</option>
                    <option value="warning" @selected(request('level') === 'warning')>Warning</option>
                    <option value="info" @selected(request('level') === 'info')>Info</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Filtrele</button>
                    <a href="/sistem-loglari" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">Temizle</a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kanal</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Seviye</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kaynak</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mesaj</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kullanıcı</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-4 py-2 text-sm">{{ $log->created_at?->format('d.m.Y H:i:s') }}</td>
                                <td class="px-4 py-2 text-sm">{{ $log->channel }}</td>
                                <td class="px-4 py-2 text-sm">{{ $log->level }}</td>
                                <td class="px-4 py-2 text-sm">{{ $log->source }}</td>
                                <td class="px-4 py-2 text-sm max-w-xl truncate" title="{{ $log->message }}">{{ $log->message }}</td>
                                <td class="px-4 py-2 text-sm">{{ $log->user_id ?? '-' }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <button
                                        type="button"
                                        class="text-blue-600 hover:underline"
                                        onclick="showLogDetail({{ $log->id }})">
                                        Detay
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">Log kaydı yok</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="log-detail-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="font-semibold">Log Detayı</h3>
                <button onclick="closeLogDetail()" class="text-gray-500 hover:text-black">Kapat</button>
            </div>
            <pre id="log-detail-content" class="p-4 text-xs overflow-auto max-h-[70vh]"></pre>
        </div>
    </div>

    <script>
        const logMap = @json($logs->keyBy('id')->map(function ($log) {
            return [
                'id' => $log->id,
                'created_at' => optional($log->created_at)->toDateTimeString(),
                'channel' => $log->channel,
                'level' => $log->level,
                'source' => $log->source,
                'message' => $log->message,
                'url' => $log->url,
                'method' => $log->method,
                'user_id' => $log->user_id,
                'ip_address' => $log->ip_address,
                'fingerprint' => $log->fingerprint,
                'exception_class' => $log->exception_class,
                'file' => $log->file,
                'line' => $log->line,
                'context' => $log->context,
            ];
        }));

        function showLogDetail(id) {
            const modal = document.getElementById('log-detail-modal');
            const content = document.getElementById('log-detail-content');
            content.textContent = JSON.stringify(logMap[id] || {}, null, 2);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeLogDetail() {
            const modal = document.getElementById('log-detail-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</body>
</html>

