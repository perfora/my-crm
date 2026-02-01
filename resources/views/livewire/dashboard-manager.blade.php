<div class="p-6 bg-white rounded-lg shadow">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold">Özel Widget Alanı</h2>
            <p class="text-sm text-gray-600 mt-1">Tüm İşler, Müşteriler, Kişiler vb. tablolarından istediğin verileri filtrele ve göster</p>
        </div>
        <button 
            wire:click="openAddWidget" 
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">
            + Yeni Widget
        </button>
    </div>

    <!-- Widget Ekleme/Düzenleme Formu -->
    @if($showWidgetForm)
    <div class="mb-8 p-6 bg-blue-50 rounded-lg border-2 border-blue-300">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-blue-900">
                {{ $editingWidget ? '✏️ Widget Düzenle' : '➕ Yeni Widget Oluştur' }}
            </h2>
            <button 
                wire:click="$set('showWidgetForm', false)" 
                class="text-gray-500 hover:text-gray-700 text-2xl">
                ✕
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Adım 1: Veri Kaynağı -->
            <div class="bg-white rounded-lg p-4 border-l-4 border-blue-600">
                <label class="block font-bold text-lg mb-3 text-blue-900">1️⃣ Tablo Seç</label>
                <select wire:model.live="selectedDataSource" class="w-full p-3 border-2 border-blue-300 rounded font-semibold bg-blue-50">
                    @foreach($this->getAvailableDataSources() as $key => $source)
                        <option value="{{ $key }}">{{ $source['label'] }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-600 mt-2">Hangi tablodan veri görmek istiyorsun?</p>
            </div>

            <!-- Adım 2: Widget Tipi -->
            <div class="bg-white rounded-lg p-4 border-l-4 border-green-600">
                <label class="block font-bold text-lg mb-3 text-green-900">2️⃣ Görünüm</label>
                <select wire:model="widgetType" class="w-full p-3 border-2 border-green-300 rounded font-semibold bg-green-50">
                    <option value="table">📊 Tablo</option>
                    <option value="metric">📈 KPI</option>
                    <option value="chart">📉 Grafik (yakında)</option>
                    <option value="calendar">📅 Takvim (yakında)</option>
                </select>
                <p class="text-xs text-gray-600 mt-2">Verileri nasıl görmek istiyorsun?</p>
            </div>

            <!-- Adım 3: Sütun Seçimi -->
            @if($selectedDataSource)
            @php
                $dataSources = $this->getAvailableDataSources();
                $columns = $dataSources[$selectedDataSource]['columns'] ?? [];
            @endphp
            @if(!empty($columns))
            <div class="bg-white rounded-lg p-4 border-l-4 border-purple-600">
                <label class="block font-bold text-lg mb-3 text-purple-900">3️⃣ Sütunlar</label>
                <div class="space-y-2 max-h-40 overflow-y-auto">
                    @foreach($columns as $key => $label)
                        <label class="flex items-center p-2 hover:bg-purple-50 rounded cursor-pointer">
                            <input
                                type="checkbox"
                                wire:click="toggleColumn('{{ $key }}')"
                                {{ in_array($key, $selectedColumns ?? []) ? 'checked' : '' }}
                                class="w-4 h-4 text-purple-600 rounded mr-2"
                            >
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-600 mt-2">Hangi sütunları görmek istiyorsun?</p>
            </div>
            @endif
            @endif
        </div>

        <!-- Filtreler (Genişletilmiş) -->
        <div class="mt-6 bg-white rounded-lg p-4 border-l-4 border-orange-600">
            <div class="flex justify-between items-center mb-4">
                <label class="block font-bold text-lg text-orange-900">🔍 Filtreler</label>
                <button wire:click="addFilter" class="px-3 py-1 bg-orange-500 text-white rounded hover:bg-orange-600 text-sm font-semibold">
                    + Filtre Ekle
                </button>
            </div>
            
            @if(!empty($selectedFilters))
                <div class="space-y-3">
                    @foreach($selectedFilters as $index => $filter)
                        <div class="p-3 bg-orange-50 rounded border-l-4 border-orange-300">
                            <div class="flex justify-between items-start mb-3">
                                <select wire:model="selectedFilters.{{ $index }}.type" class="flex-1 p-2 border border-orange-300 rounded font-semibold text-sm">
                                    @foreach($this->getAvailableFilters() as $fkey => $fvalue)
                                        <option value="{{ $fkey }}">{{ $fvalue['label'] }}</option>
                                    @endforeach
                                </select>
                                <button 
                                    wire:click="removeFilter({{ $index }})" 
                                    class="ml-2 px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                                    🗑️
                                </button>
                            </div>
                            
                            <!-- Filtre Parametreleri -->
                            @php
                                $filterType = $selectedFilters[$index]['type'] ?? 'text_search';
                                $availableFilters = $this->getAvailableFilters();
                                $filterConfig = $availableFilters[$filterType] ?? null;
                            @endphp
                            @if($filterConfig && !empty($filterConfig['params']))
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($filterConfig['params'] as $paramKey => $param)
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">{{ $param['label'] }}</label>
                                            @if($param['type'] === 'number')
                                                <input 
                                                    type="number" 
                                                    wire:model="selectedFilters.{{ $index }}.{{ $paramKey }}"
                                                    placeholder="{{ $param['default'] ?? '' }}"
                                                    class="w-full p-2 border border-orange-300 rounded text-sm"
                                                >
                                            @elseif($param['type'] === 'date')
                                                <input 
                                                    type="date" 
                                                    wire:model="selectedFilters.{{ $index }}.{{ $paramKey }}"
                                                    class="w-full p-2 border border-orange-300 rounded text-sm"
                                                >
                                            @elseif($param['type'] === 'text')
                                                <input 
                                                    type="text" 
                                                    wire:model="selectedFilters.{{ $index }}.{{ $paramKey }}"
                                                    class="w-full p-2 border border-orange-300 rounded text-sm"
                                                    placeholder="Örnek: is_adi, kar"
                                                >
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-600 italic">Filtre eklemek istersen "+ Filtre Ekle" butonuna tıkla</p>
            @endif
        </div>

        <!-- Kaydet/İptal -->
        <div class="flex gap-3 mt-6">
            <button wire:click="saveWidget" class="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-bold">
                ✅ Kaydet
            </button>
            <button wire:click="$set('showWidgetForm', false)" class="flex-1 px-4 py-3 bg-gray-400 text-white rounded-lg hover:bg-gray-500 font-bold">
                ❌ İptal
            </button>
        </div>
    </div>
    @endif

    <!-- Widget Listesi (Sürükle-Bırak) -->
    <div id="widgetContainer" class="space-y-4">
        @foreach($widgets as $widget)
        <div wire:key="widget-{{ $widget['id'] }}" data-id="{{ $widget['id'] }}" class="p-4 bg-white border rounded-lg shadow hover:shadow-lg cursor-move" draggable="true">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <h3 class="font-bold text-lg">
                        @php
                            $sources = $this->getAvailableDataSources();
                            $label = isset($sources[$widget['data_source']]) ? ($sources[$widget['data_source']]['label'] ?? $widget['data_source']) : $widget['data_source'];
                        @endphp
                        {{ $label }}
                    </h3>
                    <p class="text-sm text-gray-600">
                        Tip: <span class="font-semibold">{{ ucfirst($widget['type']) }}</span>
                        | Sütunlar: <span class="font-semibold">{{ count($widget['columns'] ?? []) }}</span>
                        | Filtreler: <span class="font-semibold">{{ count($widget['filters'] ?? []) }}</span>
                    </p>
                </div>
                <div class="flex gap-2">
                    <button 
                        wire:click="editWidget({{ $widget['id'] }})"
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm">
                        Düzenle
                    </button>
                    <button 
                        wire:click="deleteWidget({{ $widget['id'] }})"
                        onclick="confirm('Widget silinsin mi?') || event.stopImmediatePropagation()"
                        class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                        Sil
                    </button>
                </div>
            </div>

            <!-- Widget İçeriği (Preview) -->
            <div class="mt-4 border-t pt-4">
                @php
                    $data = $this->getWidgetDataForRender($widget['id']);
                    $widgetSources = $this->getAvailableDataSources();
                @endphp

                @if($widget['type'] === 'table')
                    @if(!empty($data))
                    <div class="overflow-x-auto bg-gray-50 rounded">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-200 border-b">
                                    @foreach($widget['columns'] as $col)
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            {{ $widgetSources[$widget['data_source']]['columns'][$col] ?? $col }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($data, 0, 10) as $row)
                                    <tr class="border-b hover:bg-white transition">
                                        @foreach($widget['columns'] as $col)
                                            <td class="px-4 py-3 text-gray-800">
                                                @php
                                                    $value = $row[$col] ?? '-';
                                                    // Tarihleri formatla
                                                    if(in_array($col, ['is_tarihi', 'created_at', 'updated_at', 'ziyaret_tarihi', 'lisans_bitis_tarihi']) && $value && $value !== '-') {
                                                        $value = \Carbon\Carbon::parse($value)->format('d.m.Y');
                                                    }
                                                    // Sayıları formatla (kar, teklif, alış, vb.)
                                                    if(in_array($col, ['kar', 'teklif', 'aliş']) && is_numeric($value)) {
                                                        $value = number_format($value, 0, ',', '.');
                                                    }
                                                @endphp
                                                {{ $value }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 font-semibold">📊 {{ count($data) }} kayıt toplam (ilk 10 gösteriliyor)</p>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <p class="text-lg">📭 Bu filtreyle eşleşen veri yok</p>
                        </div>
                    @endif
                @elseif($widget['type'] === 'metric')
                    <div class="grid grid-cols-4 gap-4">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded text-center">
                            <p class="text-3xl font-bold text-blue-600">{{ count($data) }}</p>
                            <p class="text-sm text-gray-600 mt-1">Toplam</p>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500 italic text-center py-8">🔄 {{ ucfirst($widget['type']) }} görünümü yakında kullanılabilir</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if(empty($widgets))
    <div class="text-center py-12 text-gray-500">
        <p class="mb-4">Henüz widget eklenmedi</p>
        <button wire:click="openAddWidget" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            İlk Widget'ı Ekle
        </button>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('widgetContainer');
    if (!container) return;
    
    let draggedElement = null;
    
    // Drag start
    container.addEventListener('dragstart', function(e) {
        if (e.target.hasAttribute('data-id')) {
            draggedElement = e.target;
            e.target.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
        }
    });
    
    // Drag over
    container.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        
        if (e.target.hasAttribute('data-id') && e.target !== draggedElement) {
            const rect = e.target.getBoundingClientRect();
            const midpoint = rect.top + rect.height / 2;
            
            if (e.clientY < midpoint) {
                e.target.parentNode.insertBefore(draggedElement, e.target);
            } else {
                e.target.parentNode.insertBefore(draggedElement, e.target.nextSibling);
            }
        }
    });
    
    // Drag end
    container.addEventListener('dragend', function(e) {
        if (draggedElement) {
            draggedElement.style.opacity = '1';
            
            // Yeni sırayı al
            const ids = Array.from(container.querySelectorAll('[data-id]')).map(el => el.getAttribute('data-id'));
            
            // Livewire'a gönder
            @this.dispatch('reorder', ids);
            draggedElement = null;
        }
    });
});
</script>
