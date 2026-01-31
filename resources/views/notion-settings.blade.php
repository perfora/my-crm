<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notion Ayarları - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    @include('layouts.nav')
    
    <div class="max-w-4xl mx-auto px-6 py-8">
        <h1 class="text-3xl font-bold mb-6">🔗 Notion Senkronizasyon Ayarları</h1>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- API Token -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">1️⃣ API Token</h2>
            <p class="text-gray-600 mb-4 text-sm">
                Notion Integration'dan aldığın API token'ı buraya gir.
                <a href="https://www.notion.so/my-integrations" target="_blank" class="text-blue-600 hover:underline">
                    🔗 Notion Integrations
                </a>
            </p>
            
            <form method="POST" action="/notion-settings/update">
                @csrf
                <input type="hidden" name="key" value="api_token">
                <div class="flex gap-3">
                    <input type="password" 
                           name="value" 
                           value="{{ $settings['api_token'] ?? '' }}"
                           placeholder="secret_xxxxxxxxxxxxxxxxxxxxx"
                           class="flex-1 border rounded px-3 py-2 font-mono text-sm">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                        💾 Kaydet
                    </button>
                </div>
            </form>
        </div>

        <!-- Database IDs -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">2️⃣ Database ID'leri</h2>
            <p class="text-gray-600 mb-4 text-sm">
                Notion'daki database'lerin URL'lerinden ID'leri kopyala. 
                <a href="javascript:void(0)" onclick="document.getElementById('help-box').classList.toggle('hidden')" class="text-blue-600 hover:underline">
                    ❓ Nasıl bulabilirim?
                </a>
            </p>

            <div id="help-box" class="hidden bg-blue-50 border border-blue-200 rounded p-4 mb-4 text-sm">
                <p class="font-bold mb-2">Database ID'yi bulmak için:</p>
                <ol class="list-decimal ml-5 space-y-1">
                    <li>Notion'da database'ini full page olarak aç</li>
                    <li>URL'ye bak: <code class="bg-white px-2 py-1 rounded">https://www.notion.so/<span class="text-red-600">abc123def456</span>?v=...</code></li>
                    <li>Kırmızı kısmı kopyala (32 karakter, tire ve harf/rakam içerir)</li>
                </ol>
            </div>

            <div class="space-y-4">
                @foreach([
                    'tum_isler_db_id' => '📋 Tüm İşler',
                    'musteriler_db_id' => '🏢 Müşteriler (Firmalar)',
                    'kisiler_db_id' => '👥 Kişiler',
                    'markalar_db_id' => '🏷️ Markalar (Ürünler)',
                    'ziyaretler_db_id' => '📅 Ziyaret Takip'
                ] as $key => $label)
                <form method="POST" action="/notion-settings/update" class="border-b pb-4">
                    @csrf
                    <input type="hidden" name="key" value="{{ $key }}">
                    <label class="block text-sm font-medium mb-2">{{ $label }}</label>
                    <div class="flex gap-3">
                        <input type="text" 
                               name="value" 
                               value="{{ $settings[$key] ?? '' }}"
                               placeholder="abc123def456789..."
                               class="flex-1 border rounded px-3 py-2 font-mono text-sm">
                        <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                            💾 Kaydet
                        </button>
                    </div>
                </form>
                @endforeach
            </div>
        </div>

        <!-- Sync Buttons -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">3️⃣ Senkronizasyon</h2>
            <p class="text-gray-600 mb-4 text-sm">
                Database ID'lerini kaydettikten sonra tek tıkla senkronize et.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Pull from Notion -->
                <div class="border-2 border-blue-200 rounded-lg p-4">
                    <h3 class="font-bold mb-3 text-blue-700">📥 Notion → Laravel</h3>
                    
                    @if(!empty($settings['musteriler_db_id']))
                    <form method="POST" action="/notion-settings/sync" class="mb-2">
                        @csrf
                        <input type="hidden" name="type" value="musteriler">
                        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm">
                            🏢 Müşterileri Çek
                        </button>
                    </form>
                    @endif

                    @if(!empty($settings['kisiler_db_id']))
                    <form method="POST" action="/notion-settings/sync" class="mb-2">
                        @csrf
                        <input type="hidden" name="type" value="kisiler">
                        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm">
                            👥 Kişileri Çek
                        </button>
                    </form>
                    @endif

                    @if(!empty($settings['markalar_db_id']))
                    <form method="POST" action="/notion-settings/sync" class="mb-2">
                        @csrf
                        <input type="hidden" name="type" value="markalar">
                        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm">
                            🏷️ Markaları Çek
                        </button>
                    </form>
                    @endif

                    @if(!empty($settings['tum_isler_db_id']))
                    <form method="POST" action="/notion-settings/sync" class="mb-2">
                        @csrf
                        <input type="hidden" name="type" value="tum-isler">
                        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm">
                            📋 Tüm İşleri Çek
                        </button>
                    </form>
                    @endif

                    @if(!empty($settings['ziyaretler_db_id']))
                    <form method="POST" action="/notion-settings/sync" class="mb-2">
                        @csrf
                        <input type="hidden" name="type" value="ziyaretler">
                        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm">
                            📅 Ziyaretleri Çek
                        </button>
                    </form>
                    @endif

                    @if(empty($settings['tum_isler_db_id']) && empty($settings['musteriler_db_id']))
                    <p class="text-gray-500 text-sm">Database ID'leri gir</p>
                    @endif
                </div>

                <!-- Push to Notion -->
                <div class="border-2 border-green-200 rounded-lg p-4">
                    <h3 class="font-bold mb-3 text-green-700">📤 Laravel → Notion</h3>
                    
                    @if(!empty($settings['musteriler_db_id']))
                    <form method="POST" action="/notion-settings/push" class="mb-2">
                        @csrf
                        <input type="hidden" name="type" value="musteriler">
                        <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm">
                            🏢 Müşterileri Gönder
                        </button>
                    </form>
                    @endif

                    @if(!empty($settings['kisiler_db_id']))
                    <form method="POST" action="/notion-settings/push" class="mb-2">
                        @csrf
                        <input type="hidden" name="type" value="kisiler">
                        <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm">
                            👥 Kişileri Gönder
                        </button>
                    </form>
                    @endif

                    @if(!empty($settings['markalar_db_id']))
                    <form method="POST" action="/notion-settings/push" class="mb-2">
                        @csrf
                        <input type="hidden" name="type" value="markalar">
                        <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm">
                            🏷️ Markaları Gönder
                        </button>
                    </form>
                    @endif

                    @if(!empty($settings['tum_isler_db_id']))
                    <form method="POST" action="/notion-settings/push" class="mb-2">
                        @csrf
                        <input type="hidden" name="type" value="tum-isler">
                        <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm">
                            📋 Tüm İşleri Gönder
                        </button>
                    </form>
                    @endif

                    @if(!empty($settings['ziyaretler_db_id']))
                    <form method="POST" action="/notion-settings/push" class="mb-2">
                        @csrf
                        <input type="hidden" name="type" value="ziyaretler">
                        <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm">
                            📅 Ziyaretleri Gönder
                        </button>
                    </form>
                    @endif

                    @if(empty($settings['tum_isler_db_id']) && empty($settings['musteriler_db_id']))
                    <p class="text-gray-500 text-sm">Database ID'leri gir</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- İstatistikler -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">📊 Senkronizasyon İstatistikleri</h2>
            
            @php
                $notionIsler = \App\Models\TumIsler::whereNotNull('notion_id')->count();
                $totalIsler = \App\Models\TumIsler::count();
                $notionMusteriler = \App\Models\Musteri::whereNotNull('notion_id')->count();
                $totalMusteriler = \App\Models\Musteri::count();
                $notionKisiler = \App\Models\Kisi::whereNotNull('notion_id')->count();
                $totalKisiler = \App\Models\Kisi::count();
                $notionMarkalar = \App\Models\Marka::whereNotNull('notion_id')->count();
                $totalMarkalar = \App\Models\Marka::count();
                $notionZiyaretler = \App\Models\Ziyaret::whereNotNull('notion_id')->count();
                $totalZiyaretler = \App\Models\Ziyaret::count();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border rounded-lg p-4 text-center">
                    <p class="text-gray-600 text-sm mb-2">📋 Tüm İşler</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $notionIsler }}</p>
                    <p class="text-xs text-gray-500 mt-1">/ {{ $totalIsler }} toplam</p>
                </div>
                
                <div class="border rounded-lg p-4 text-center">
                    <p class="text-gray-600 text-sm mb-2">🏢 Müşteriler</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $notionMusteriler }}</p>
                    <p class="text-xs text-gray-500 mt-1">/ {{ $totalMusteriler }} toplam</p>
                </div>
                
                <div class="border rounded-lg p-4 text-center">
                    <p class="text-gray-600 text-sm mb-2">👥 Kişiler</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $notionKisiler }}</p>
                    <p class="text-xs text-gray-500 mt-1">/ {{ $totalKisiler }} toplam</p>
                </div>
                
                <div class="border rounded-lg p-4 text-center">
                    <p class="text-gray-600 text-sm mb-2">🏷️ Markalar</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $notionMarkalar }}</p>
                    <p class="text-xs text-gray-500 mt-1">/ {{ $totalMarkalar }} toplam</p>
                </div>
                
                <div class="border rounded-lg p-4 text-center">
                    <p class="text-gray-600 text-sm mb-2">📅 Ziyaretler</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $notionZiyaretler }}</p>
                    <p class="text-xs text-gray-500 mt-1">/ {{ $totalZiyaretler }} toplam</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
