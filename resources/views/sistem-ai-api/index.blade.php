<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI API Yönetimi - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    @include('layouts.nav')

    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold">AI API Yönetimi</h1>
            <span class="text-sm text-gray-600">Read-Only Faz-1</span>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if(session('generated_token'))
            <div class="mb-6 rounded border border-amber-300 bg-amber-50 px-4 py-3">
                <p class="font-semibold text-amber-900 mb-2">Token bir kez gösterilir (ID: {{ session('generated_token_id') }})</p>
                <code class="block text-sm bg-white border rounded px-3 py-2 break-all">{{ session('generated_token') }}</code>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <h2 class="text-lg font-semibold mb-4">Yeni Token Oluştur</h2>
            <form method="POST" action="{{ route('system.ai-api.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Token Adı</label>
                    <input type="text" name="name" class="w-full border rounded px-3 py-2" placeholder="chatgpt-readonly" required>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Kullanıcı</label>
                    <select name="user_id" class="w-full border rounded px-3 py-2">
                        <option value="">Bağlama (opsiyonel)</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Scope</label>
                    <label class="flex items-center gap-2 border rounded px-3 py-2">
                        <input type="checkbox" name="scopes[]" value="crm.read" checked>
                        <span>crm.read</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Geçerlilik (gün)</label>
                    <input type="number" name="expires_days" min="1" max="3650" value="30" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Token Oluştur</button>
                </div>
            </form>
            @if($errors->any())
                <div class="mt-3 text-sm text-red-600">
                    {{ $errors->first() }}
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-4 py-3 border-b font-semibold">Tokenlar</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ad</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kullanıcı</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Scope</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Son Kullanım</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bitiş</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tokens as $token)
                            <tr>
                                <td class="px-4 py-2 text-sm">{{ $token->id }}</td>
                                <td class="px-4 py-2 text-sm">{{ $token->name }}</td>
                                <td class="px-4 py-2 text-sm">{{ $token->user?->name ?? '-' }}</td>
                                <td class="px-4 py-2 text-sm">{{ implode(', ', $token->scopes ?? []) }}</td>
                                <td class="px-4 py-2 text-sm">
                                    @if($token->is_active)
                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Aktif</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Pasif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm">{{ optional($token->last_used_at)?->format('d.m.Y H:i') ?? '-' }}</td>
                                <td class="px-4 py-2 text-sm">{{ optional($token->expires_at)?->format('d.m.Y H:i') ?? '-' }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <form method="POST" action="{{ route('system.ai-api.toggle', $token->id) }}">
                                        @csrf
                                        <button type="submit" class="text-blue-600 hover:underline">
                                            {{ $token->is_active ? 'Pasif Et' : 'Aktif Et' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">Henüz token yok</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-4 py-3 border-b font-semibold">AI Read Endpointleri</div>
            <div class="p-4 text-sm space-y-1">
                <p><code>GET /api/ai/summary/dashboard</code></p>
                <p><code>GET /api/ai/tum-isler</code></p>
                <p><code>GET /api/ai/musteriler</code></p>
                <p><code>GET /api/ai/ziyaretler</code></p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-3 border-b font-semibold">Son AI Audit Logları</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Token</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Süre</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-4 py-2 text-sm">{{ $log->created_at?->format('d.m.Y H:i:s') }}</td>
                                <td class="px-4 py-2 text-sm">{{ $log->ai_api_token_id ?? '-' }}</td>
                                <td class="px-4 py-2 text-sm">{{ $log->action }}</td>
                                <td class="px-4 py-2 text-sm truncate max-w-xl" title="{{ $log->url }}">{{ $log->url }}</td>
                                <td class="px-4 py-2 text-sm">{{ $log->status_code ?? '-' }}</td>
                                <td class="px-4 py-2 text-sm">{{ $log->duration_ms ? $log->duration_ms.' ms' : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">Henüz audit log yok</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

