<div class="p-6 bg-white rounded-lg shadow">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <button 
            wire:click="openAddWidget" 
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            + Widget Ekle
        </button>
    </div>

    <!-- Widget Ekleme/Düzenleme Formu -->
    @if($showWidgetForm)
    <div class="mb-6 p-4 bg-gray-100 rounded-lg border-l-4 border-blue-600">
        <h2 class="text-xl font-bold mb-4">{{ $editingWidget ? 'Widget Düzenle' : 'Yeni Widget' }}</h2>

        <!-- Veri Kaynağı Seçimi -->
        <div class="mb-4">
            <label class="block font-semibold mb-2">Veri Kaynağı</label>
            <select wire:model.live="selectedDataSource" class="w-full p-2 border rounded">
                @foreach($this->getAvailableDataSources() as $key => $source)
                    <option value="{{ $key }}">{{ $source['label'] }}</option>
                @endforeach
            </select>
        </div>

        <!-- Widget Tipi -->
        <div class="mb-4">
            <label class="block font-semibold mb-2">Görünüm Tipi</label>
            <select wire:model="widgetType" class="w-full p-2 border rounded">
                <option value="table">Tablo</option>
                <option value="chart">Grafik</option>
                <option value="calendar">Takvim</option>
                <option value="metric">KPI</option>
            </select>
        </div>

        <!-- Sütun Seçimi -->
        @if($selectedDataSource)
        <div class="mb-4">
            <label class="block font-semibold mb-2">Sütunlar</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach($this->getAvailableDataSources()[$selectedDataSource]['columns'] as $key => $label)
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            wire:click="toggleColumn('{{ $key }}')"
                            {{ in_array($key, $selectedColumns ?? []) ? 'checked' : '' }}
                            class="mr-2"
                        >
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Filtre Seçimi -->
        <div class="mb-4">
            <label class="block font-semibold mb-2">Filtreler</label>
            @foreach($selectedFilters as $index => $filter)
                <div class="mb-3 p-3 bg-white rounded border">
                    <div class="flex justify-between mb-2">
                        <select wire:model="selectedFilters.{{ $index }}.type" class="flex-1 p-2 border rounded mr-2">
                            @foreach($this->getAvailableFilters() as $fkey => $fvalue)
                                <option value="{{ $fkey }}">{{ $fvalue['label'] }}</option>
                            @endforeach
                        </select>
                        <button wire:click="removeFilter({{ $index }})" class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                            Sil
                        </button>
                    </div>
                    
                    <!-- Filtre Parametreleri -->
                    @php $filterConfig = $this->getAvailableFilters()[$selectedFilters[$index]['type'] ?? 'text_search'] ?? null; @endphp
                    @if($filterConfig && !empty($filterConfig['params']))
                        @foreach($filterConfig['params'] as $paramKey => $param)
                            <div class="mb-2">
                                <label class="block text-sm mb-1">{{ $param['label'] }}</label>
                                @if($param['type'] === 'number')
                                    <input 
                                        type="number" 
                                        wire:model="selectedFilters.{{ $index }}.{{ $paramKey }}"
                                        class="w-full p-2 border rounded"
                                    >
                                @elseif($param['type'] === 'date')
                                    <input 
                                        type="date" 
                                        wire:model="selectedFilters.{{ $index }}.{{ $paramKey }}"
                                        class="w-full p-2 border rounded"
                                    >
                                @elseif($param['type'] === 'text')
                                    <input 
                                        type="text" 
                                        wire:model="selectedFilters.{{ $index }}.{{ $paramKey }}"
                                        class="w-full p-2 border rounded"
                                    >
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            @endforeach
            <button wire:click="addFilter" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                + Filtre Ekle
            </button>
        </div>

        <!-- Kaydet/İptal -->
        <div class="flex gap-2">
            <button wire:click="saveWidget" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Kaydet
            </button>
            <button wire:click="$set('showWidgetForm', false)" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">
                İptal
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
                            $label = $sources[$widget['data_source']]['label'] ?? $widget['data_source'];
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
                @php $data = $this->getWidgetDataForRender($widget['id']); @endphp
                
                @if($widget['type'] === 'table')
                    @if(!empty($data))
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-200">
                                    @foreach($widget['columns'] as $col)
                                        <th class="border p-2 text-left">
                                            {{ $sources[$widget['data_source']]['columns'][$col] ?? $col }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($data, 0, 5) as $row)
                                    <tr class="border-b hover:bg-gray-50">
                                        @foreach($widget['columns'] as $col)
                                            <td class="border p-2">
                                                {{ $row[$col] ?? '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Toplam: {{ count($data) }} kayıt (ilk 5 gösteriliyor)</p>
                    @else
                        <p class="text-gray-500 italic">Veri yok</p>
                    @endif
                @elseif($widget['type'] === 'metric')
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-blue-100 p-4 rounded text-center">
                            <p class="text-2xl font-bold text-blue-700">{{ count($data) }}</p>
                            <p class="text-sm text-gray-600">Toplam Kayıt</p>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500 italic">{{ ucfirst($widget['type']) }} tipi henüz başlangıç aşamasında</p>
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
