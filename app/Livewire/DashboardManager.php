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
    public $dashboard;
    public $widgets = [];
    public $showWidgetForm = false;
    public $editingWidget = null;
    public $dataService;
    public $selectedDataSource = 'tum_isler';
    public $selectedColumns = [];
    public $selectedFilters = [];
    public $widgetType = 'table';

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
        $this->selectedFilters[] = ['type' => 'text_search', 'field' => 'name', 'value' => ''];
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
        return $this->dataService->getAvailableDataSources();
    }

    public function getAvailableFilters()
    {
        return $this->dataService->getAvailableFilters();
    }

    public function getWidgetDataForRender($widgetId)
    {
        $widget = DashboardWidget::find($widgetId);
        if (!$widget) return [];

        return $this->dataService->getWidgetData(
            $widget->data_source,
            $widget->filters ?? [],
            $widget->columns ?? []
        );
    }

    public function render()
    {
        return view('livewire.dashboard-manager');
    }
}
