<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Dashboard;
use App\Models\DashboardWidget;
use App\Services\DashboardDataService;
use Illuminate\Support\Facades\Auth;

class DashboardManager extends Component
{
    public ?Dashboard $dashboard = null;
    public array $widgets = [];
    public bool $showWidgetForm = false;
    public ?int $editingWidget = null;
    public string $selectedDataSource = 'tum_isler';
    public array $selectedColumns = [];
    public array $selectedFilters = [];
    public string $widgetType = 'table';
    
    private ?DashboardDataService $dataService = null;

    #[On('reorder')]
    public function handleReorder($ids)
    {
        $this->updateOrder($ids);
    }

    public function mount()
    {
        $this->dataService = new DashboardDataService(app(\App\Services\DashboardFilterService::class));
        $this->loadDashboard();
    }
    
    private function getDataService(): DashboardDataService
    {
        if (!$this->dataService) {
            $this->dataService = new DashboardDataService(app(\App\Services\DashboardFilterService::class));
        }
        return $this->dataService;
    }

    public function loadDashboard()
    {
        $this->dashboard = Dashboard::where('user_id', Auth::id())
            ->where('is_default', true)
            ->first();

        if (!$this->dashboard) {
            $this->dashboard = Dashboard::create([
                'user_id' => Auth::id(),
                'name' => 'Varsayılan',
                'is_default' => true,
            ]);
        }

        $this->widgets = $this->dashboard->widgets()->orderBy('order')->get()->toArray();
    }

    public function openAddWidget()
    {
        $this->showWidgetForm = true;
        $this->resetWidgetForm();
    }

    public function resetWidgetForm()
    {
        $this->editingWidget = null;
        $this->selectedDataSource = 'tum_isler';
        $this->selectedColumns = [];
        $this->selectedFilters = [];
        $this->widgetType = 'table';
    }

    public function editWidget($widgetId)
    {
        $widget = DashboardWidget::find($widgetId);
        if (!$widget) return;

        $this->editingWidget = $widget->id;
        $this->selectedDataSource = $widget->data_source;
        $this->selectedColumns = $widget->columns ?? [];
        $this->selectedFilters = $widget->filters ?? [];
        $this->widgetType = $widget->type;
        $this->showWidgetForm = true;
    }

    public function saveWidget()
    {
        if (empty($this->selectedDataSource)) {
            $this->addError('selectedDataSource', 'Veri kaynağı seçiniz');
            return;
        }

        if ($this->editingWidget) {
            $widget = DashboardWidget::find($this->editingWidget);
            $widget->update([
                'type' => $this->widgetType,
                'data_source' => $this->selectedDataSource,
                'columns' => $this->selectedColumns ?: [],
                'filters' => $this->selectedFilters ?: [],
            ]);
        } else {
            DashboardWidget::create([
                'dashboard_id' => $this->dashboard->id,
                'type' => $this->widgetType,
                'data_source' => $this->selectedDataSource,
                'columns' => $this->selectedColumns ?: [],
                'filters' => $this->selectedFilters ?: [],
                'order' => DashboardWidget::where('dashboard_id', $this->dashboard->id)->count(),
            ]);
        }

        $this->showWidgetForm = false;
        $this->loadDashboard();
    }

    public function deleteWidget($widgetId)
    {
        DashboardWidget::find($widgetId)?->delete();
        $this->loadDashboard();
    }

    public function updateOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            DashboardWidget::find($id)?->update(['order' => $index]);
        }
        $this->loadDashboard();
    }

    public function addFilter()
    {
        $this->selectedFilters[] = ['type' => 'status', 'field' => '', 'value' => ''];
    }

    public function removeFilter($index)
    {
        unset($this->selectedFilters[$index]);
        $this->selectedFilters = array_values($this->selectedFilters);
    }

    public function toggleColumn($columnKey)
    {
        if (in_array($columnKey, $this->selectedColumns ?? [])) {
            $this->selectedColumns = array_filter($this->selectedColumns, fn($c) => $c !== $columnKey);
        } else {
            $this->selectedColumns[] = $columnKey;
        }
    }

    public function getAvailableDataSources()
    {
        return $this->getDataService()->getAvailableDataSources();
    }

    public function getAvailableFilters()
    {
        return $this->getDataService()->getAvailableFilters();
    }

    public function getWidgetDataForRender($widgetId)
    {
        $widget = DashboardWidget::find($widgetId);
        if (!$widget) return [];

        return $this->getDataService()->getWidgetData(
            $widget->data_source,
            $widget->filters ?? [],
            $widget->columns ?? []
        );
    }

    public function getFieldValues(string $dataSource, string $field): array
    {
        try {
            $query = $this->getDataService()->getBaseQuery($dataSource);
            $filterService = app(\App\Services\DashboardFilterService::class);
            return $filterService->getDistinctValues($query, $field);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getSelectedColumnsWithLabels(): array
    {
        if (!$this->selectedDataSource || empty($this->selectedColumns)) {
            return [];
        }

        $sources = $this->getDataService()->getAvailableDataSources();
        $columns = $sources[$this->selectedDataSource]['columns'] ?? [];
        
        $result = [];
        foreach ($this->selectedColumns as $col) {
            if (isset($columns[$col])) {
                $result[$col] = $columns[$col];
            }
        }
        return $result;
    }

    public function render()
    {
        return view('livewire.dashboard-manager');
    }
}
