<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ana Sayfa - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    @include('layouts.nav')
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">CRM Dashboard</h1>
            <div class="flex gap-2">
                <a href="/dashboard/widget-settings" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
                    ⚙️ Widget Ayarları
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        🚪 Çıkış Yap
                    </button>
                </form>
            </div>
        </div>
        
        @php
            $mevcutYil = date('Y'); // 2026
            
            $musteriler = \App\Models\Musteri::all();
            $kisiler = \App\Models\Kisi::all();
            $ziyaretler = \App\Models\Ziyaret::where('durumu', 'Tamamlandı')
                ->whereYear('ziyaret_tarihi', $mevcutYil)
                ->get();
            $isler = \App\Models\TumIsler::where('tipi', 'Verildi')
                ->whereYear('is_guncellenme_tarihi', $mevcutYil)
                ->get();
            
            // 2025 ve 2026 kazanılan işler
            $kazanilan2025 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
                ->whereYear('kapanis_tarihi', 2025)
                ->get();
            
            $kazanilan2026 = \App\Models\TumIsler::where('tipi', 'Kazanıldı')
                ->whereYear('kapanis_tarihi', 2026)
                ->get();
            
            $toplamTeklif2025 = 0;
            $toplamAlis2025 = 0;
            $toplamTeklif2026 = 0;
            $toplamAlis2026 = 0;
            
            foreach($kazanilan2025 as $is) {
                if($is->teklif_doviz === 'USD' && $is->teklif_tutari) {
                    $toplamTeklif2025 += $is->teklif_tutari;
                }
                if($is->alis_doviz === 'USD' && $is->alis_tutari) {
                    $toplamAlis2025 += $is->alis_tutari;
                }
            }
            
            foreach($kazanilan2026 as $is) {
                if($is->teklif_doviz === 'USD' && $is->teklif_tutari) {
                    $toplamTeklif2026 += $is->teklif_tutari;
                }
                if($is->alis_doviz === 'USD' && $is->alis_tutari) {
                    $toplamAlis2026 += $is->alis_tutari;
                }
            }
            
            $kar2025 = $toplamTeklif2025 - $toplamAlis2025;
            $karOrani2025 = $toplamTeklif2025 > 0 ? ($kar2025 / $toplamTeklif2025 * 100) : 0;
            $kar2026 = $toplamTeklif2026 - $toplamAlis2026;
            $karOrani2026 = $toplamTeklif2026 > 0 ? ($kar2026 / $toplamTeklif2026 * 100) : 0;
            
            // Notion'dan senkronize edilmiş kayıtlar
            $notionIsler = \App\Models\TumIsler::whereNotNull('notion_id')->count();
            $notionMusteriler = \App\Models\Musteri::whereNotNull('notion_id')->count();
            
            // Widget ayarlarını dosyadan oku
            $settingsFile = storage_path('app/widget-settings.json');
            if(file_exists($settingsFile)) {
                $settings = json_decode(file_get_contents($settingsFile), true);
                $widgets = $settings ?? [];
            } else {
                // Default ayarlar
                $widgets = [
                    'ozet_kartlar' => true,
                    'yillik_karsilastirma' => true,
                    'bekleyen_isler' => true,
                    'bu_ay_kazanilan' => true,
                    'yuksek_oncelikli' => true,
                    'yaklasan_ziyaretler' => true,
                ];
            }
        @endphp
        
        <!-- Özet Kartlar -->
        @if($widgets['ozet_kartlar'])
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Toplam Müşteri</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $musteriler->count() }}</p>
                    </div>
                    <div class="text-4xl">🏢</div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Toplam Kişi</p>
                        <p class="text-3xl font-bold text-green-600">{{ $kisiler->count() }}</p>
                    </div>
                    <div class="text-4xl">👥</div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">2026 Tamamlanan Ziyaret</p>
                        <p class="text-3xl font-bold text-purple-600">{{ $ziyaretler->count() }}</p>
                    </div>
                    <div class="text-4xl">📅</div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">2026 Verilen İş</p>
                        <p class="text-3xl font-bold text-orange-600">{{ $isler->count() }}</p>
                    </div>
                    <div class="text-4xl">💼</div>
                </div>
            </div>
            
            @if($notionIsler > 0 || $notionMusteriler > 0)
            <div class="bg-white rounded-lg shadow p-6 border-2 border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Notion'dan Senkronize</p>
                        <div class="flex gap-3 mt-2">
                            <div>
                                <p class="text-xs text-gray-400">İşler</p>
                                <p class="text-2xl font-bold text-purple-600">{{ $notionIsler }}</p>
                            </div>
                            <div class="border-l pl-3">
                                <p class="text-xs text-gray-400">Firmalar</p>
                                <p class="text-2xl font-bold text-purple-600">{{ $notionMusteriler }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-4xl">🔗</div>
                </div>
            </div>
            @endif
        </div>
        @endif
        
        <!-- Yıllık Kazanç Karşılaştırma -->
        @if($widgets['yillik_karsilastirma'])
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">2025 Kazanılan İşler</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">İş Sayısı:</span>
                        <span class="font-bold text-lg">{{ $kazanilan2025->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Toplam Teklif:</span>
                        <span class="font-bold text-lg text-blue-600">${{ number_format($toplamTeklif2025, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t">
                        <span class="text-gray-600">Toplam Kar:</span>
                        <div>
                            <span class="font-bold text-lg text-green-600">${{ number_format($kar2025, 2) }}</span>
                            <span class="text-sm text-gray-500">({{ number_format($karOrani2025, 1) }}%)</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">2026 Kazanılan İşler</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">İş Sayısı:</span>
                        <span class="font-bold text-lg">{{ $kazanilan2026->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Toplam Teklif:</span>
                        <span class="font-bold text-lg text-blue-600">${{ number_format($toplamTeklif2026, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t">
                        <span class="text-gray-600">Toplam Kar:</span>
                        <div>
                            <span class="font-bold text-lg text-green-600">${{ number_format($kar2026, 2) }}</span>
                            <span class="text-sm text-gray-500">({{ number_format($karOrani2026, 1) }}%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Kayıtlı Filtrelerden Widget Ekle -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">📊 Kayıtlı Filtrelerden Widget Ekle</h2>
            <div id="filter-widgets-list" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- JavaScript ile doldurulacak -->
            </div>
        </div>

        <!-- Özel Filter Widgets -->
        <div id="custom-filter-widgets" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <!-- Widget'lar buraya eklenecek -->
        </div>

        <!-- Widget Alanı -->
        <div class="space-y-6">
            
            @if($widgets['bekleyen_isler'])
                @include('widgets.bekleyen-isler')
            @endif
            
            <!-- Lisans Yenilenecek İşler -->
            @include('widgets.lisans-yenilenecek')
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @if($widgets['yuksek_oncelikli'])
                    <div>@include('widgets.yuksek-oncelikli')</div>
                @endif
            </div>
            
            <!-- Ziyaret Edilecekler -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @if($widgets['yaklasan_ziyaretler'])
                    <div>@include('widgets.yaklasan-ziyaretler')</div>
                @endif
                
                <!-- Uzun Süredir Ziyaret Edilmeyen Müşteriler -->
                <div>@include('widgets.uzun-sure-ziyaret-edilmeyen')</div>
            </div>
            
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Sayfa yüklendiğinde kayıtlı filtreleri göster
        $(document).ready(function() {
            loadFilterWidgetsList();
            loadActiveWidgets();
        });

        function loadFilterWidgetsList() {
            const savedFilters = JSON.parse(localStorage.getItem('tumIslerFilters') || '{}');
            const activeWidgets = JSON.parse(localStorage.getItem('dashboardWidgets') || '[]');
            const container = $('#filter-widgets-list');
            
            container.empty();
            
            if (Object.keys(savedFilters).length === 0) {
                container.html('<p class="text-gray-500 col-span-3">Henüz kayıtlı filtre yok. Tüm İşler sayfasından filtre oluşturup kaydedin.</p>');
                return;
            }
            
            Object.keys(savedFilters).forEach(filterName => {
                const isActive = activeWidgets.includes(filterName);
                const buttonClass = isActive ? 'bg-green-500 hover:bg-green-600' : 'bg-blue-500 hover:bg-blue-600';
                const buttonText = isActive ? '✓ Eklendi' : '+ Widget Ekle';
                
                const card = $(`
                    <div class="border rounded-lg p-4 ${isActive ? 'bg-green-50 border-green-300' : 'border-gray-200'}">
                        <h3 class="font-semibold mb-2">${filterName}</h3>
                        <button 
                            onclick="toggleFilterWidget('${filterName}')" 
                            class="${buttonClass} text-white px-3 py-1 rounded text-sm w-full">
                            ${buttonText}
                        </button>
                    </div>
                `);
                
                container.append(card);
            });
        }

        function toggleFilterWidget(filterName) {
            let activeWidgets = JSON.parse(localStorage.getItem('dashboardWidgets') || '[]');
            
            if (activeWidgets.includes(filterName)) {
                // Kaldır
                activeWidgets = activeWidgets.filter(w => w !== filterName);
            } else {
                // Ekle
                activeWidgets.push(filterName);
            }
            
            localStorage.setItem('dashboardWidgets', JSON.stringify(activeWidgets));
            loadFilterWidgetsList();
            loadActiveWidgets();
        }

        function loadActiveWidgets() {
            const activeWidgets = JSON.parse(localStorage.getItem('dashboardWidgets') || '[]');
            const savedFilters = JSON.parse(localStorage.getItem('tumIslerFilters') || '{}');
            const container = $('#custom-filter-widgets');
            
            container.empty();
            
            if (activeWidgets.length === 0) {
                return;
            }
            
            activeWidgets.forEach(filterName => {
                const filterData = savedFilters[filterName];
                if (!filterData) return;
                
                // Widget kartı oluştur
                const widget = $(`
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-6 relative">
                        <button onclick="removeWidget('${filterName}')" 
                                class="absolute top-2 right-2 text-white hover:text-red-200 text-xl">
                            ×
                        </button>
                        <h3 class="text-lg font-bold mb-4">${filterName}</h3>
                        <div id="widget-${filterName.replace(/\s+/g, '-')}" class="space-y-2">
                            <div class="text-center py-4">
                                <div class="animate-spin inline-block w-6 h-6 border-2 border-white border-t-transparent rounded-full"></div>
                                <p class="mt-2 text-sm">Yükleniyor...</p>
                            </div>
                        </div>
                    </div>
                `);
                
                container.append(widget);
                
                // AJAX ile veri çek
                $.ajax({
                    url: '/api/filter-widget-data',
                    method: 'POST',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: JSON.stringify({ filterData: filterData }),
                    success: function(data) {
                        const widgetId = `widget-${filterName.replace(/\s+/g, '-')}`;
                        $(`#${widgetId}`).html(`
                            <div class="flex justify-between items-center border-b border-blue-400 pb-2">
                                <span>İş Sayısı:</span>
                                <span class="text-2xl font-bold">${data.count}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-blue-400 pb-2">
                                <span>Toplam Teklif:</span>
                                <span class="text-xl font-bold">$${data.totalTeklif}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Toplam Kar:</span>
                                <span class="text-xl font-bold text-green-200">$${data.totalKar}</span>
                            </div>
                        `);
                    },
                    error: function() {
                        const widgetId = `widget-${filterName.replace(/\s+/g, '-')}`;
                        $(`#${widgetId}`).html('<p class="text-red-200">Veri yüklenemedi</p>');
                    }
                });
            });
        }

        function removeWidget(filterName) {
            if (!confirm(`"${filterName}" widget'ını kaldırmak istediğinizden emin misiniz?`)) {
                return;
            }
            
            let activeWidgets = JSON.parse(localStorage.getItem('dashboardWidgets') || '[]');
            activeWidgets = activeWidgets.filter(w => w !== filterName);
            localStorage.setItem('dashboardWidgets', JSON.stringify(activeWidgets));
            
            loadFilterWidgetsList();
            loadActiveWidgets();
        }
    </script>
</body>
</html>