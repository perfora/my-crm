<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widget Ayarları - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
</head>
<body class="bg-gray-100">
    @include('layouts.nav')
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Widget Ayarları</h1>
            <a href="/" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                ← Dashboard'a Dön
            </a>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <form method="POST" action="/dashboard/widget-settings">
            @csrf
            
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Widget'ları Açma/Kapama</h2>
                <p class="text-gray-600 mb-4">İstediğin widget'ları aktif/pasif yapabilirsin</p>
                
                <div class="space-y-3">
                    @php
                        $availableWidgets = [
                            'ozet_kartlar' => ['name' => 'Özet Kartlar', 'desc' => 'Toplam Müşteri, Kişi, Ziyaret, İş sayıları'],
                            'yillik_karsilastirma' => ['name' => 'Yıllık Karşılaştırma', 'desc' => '2025 vs 2026 kazanılan işler'],
                            'bekleyen_isler' => ['name' => 'Bekleyen İşler', 'desc' => 'Verilecek/Takip edilecek işler listesi'],
                            'lisans_yenilenecek' => ['name' => 'Lisans Yenilenecek', 'desc' => 'Gelecek 3 ayda lisansı bitecek işler'],
                            'bu_ay_kazanilan' => ['name' => 'Bu Ay Kazanılan', 'desc' => 'Bu ayki kazanılan işler ve toplam'],
                            'yuksek_oncelikli' => ['name' => 'Yüksek Öncelikli', 'desc' => 'Öncelik 1 olan işler'],
                            'yaklasan_ziyaretler' => ['name' => 'Yaklaşan Ziyaretler', 'desc' => 'Planlanmış ziyaretler'],
                        ];
                        
                        $settingsFile = storage_path('app/widget-settings.json');
                        $currentSettings = [];
                        if(file_exists($settingsFile)) {
                            $currentSettings = json_decode(file_get_contents($settingsFile), true) ?? [];
                        }
                        
                        foreach($availableWidgets as $key => $widget) {
                            if(!isset($currentSettings[$key])) {
                                $currentSettings[$key] = true; // Default açık
                            }
                        }
                    @endphp
                    
                    @foreach($availableWidgets as $key => $widget)
                    <div class="flex items-start p-4 border rounded hover:bg-gray-50">
                        <input type="checkbox" 
                               name="widgets[{{ $key }}]" 
                               id="widget_{{ $key }}"
                               class="mt-1 mr-3 h-5 w-5 text-blue-600"
                               {{ $currentSettings[$key] ? 'checked' : '' }}>
                        <label for="widget_{{ $key }}" class="flex-1 cursor-pointer">
                            <div class="font-semibold text-gray-900">{{ $widget['name'] }}</div>
                            <div class="text-sm text-gray-600">{{ $widget['desc'] }}</div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Widget Sıralaması</h2>
                <p class="text-gray-600 mb-4">Sürükle-bırak ile widget sırasını değiştirebilirsin</p>
                
                <ul id="sortable-widgets" class="space-y-2">
                    @php
                        $order = $currentSettings['order'] ?? array_keys($availableWidgets);
                    @endphp
                    
                    @foreach($order as $key)
                        @if(isset($availableWidgets[$key]))
                        <li class="p-4 bg-gray-50 border rounded cursor-move hover:bg-gray-100" data-widget="{{ $key }}">
                            <div class="flex items-center">
                                <span class="mr-3">☰</span>
                                <span class="font-semibold">{{ $availableWidgets[$key]['name'] }}</span>
                            </div>
                        </li>
                        @endif
                    @endforeach
                </ul>
                
                <input type="hidden" name="order" id="widget-order" value="">
            </div>
            
            <div class="flex gap-4">
                <button type="submit" class="bg-green-500 text-white px-8 py-3 rounded hover:bg-green-600 font-semibold">
                    💾 Kaydet
                </button>
                <a href="/" class="bg-gray-300 text-gray-700 px-8 py-3 rounded hover:bg-gray-400 font-semibold inline-block">
                    İptal
                </a>
            </div>
        </form>
    </div>
    
    <script>
        $(function() {
            // Sortable widget listesi
            $("#sortable-widgets").sortable({
                update: function() {
                    var order = [];
                    $("#sortable-widgets li").each(function() {
                        order.push($(this).data('widget'));
                    });
                    $("#widget-order").val(JSON.stringify(order));
                }
            });
            
            // İlk yüklemede order'ı ayarla
            $("#sortable-widgets").sortable('refresh');
            var order = [];
            $("#sortable-widgets li").each(function() {
                order.push($(this).data('widget'));
            });
            $("#widget-order").val(JSON.stringify(order));
        });
    </script>
</body>
</html>
