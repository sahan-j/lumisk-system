<?php

namespace App\Livewire\Admin;

use App\Models\DashboardPreference;
use App\Services\DashboardWidgetService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public array $layout = [];
    public array $widgetData = [];
    public bool $editMode = false;
    public array $availableWidgets = [];

    public function mount(): void
    {
        $this->availableWidgets = DashboardWidgetService::getAvailableWidgets();
        $this->loadLayout();
        $this->loadWidgetData();
    }

    private function loadLayout(): void
    {
        $pref = DashboardPreference::where('user_id', auth()->id())->first();

        $layout = $pref?->widget_layout ?: DashboardWidgetService::getDefaultLayout();

        // Drop any widgets that no longer exist in the catalogue.
        $layout = array_values(array_filter($layout, fn ($w) => isset($this->availableWidgets[$w['id']])));

        usort($layout, fn ($a, $b) => ($a['position'] ?? 0) <=> ($b['position'] ?? 0));

        $this->layout = $layout;
    }

    private function loadWidgetData(): void
    {
        $this->widgetData = [];
        foreach ($this->layout as $widget) {
            if ($widget['visible'] ?? false) {
                $this->widgetData[$widget['id']] = DashboardWidgetService::getWidgetData($widget['id']);
            }
        }
    }

    public function toggleEditMode(): void
    {
        $this->editMode = ! $this->editMode;
    }

    public function toggleWidget(string $widgetId): void
    {
        foreach ($this->layout as &$widget) {
            if ($widget['id'] === $widgetId) {
                $widget['visible'] = ! $widget['visible'];
                if ($widget['visible']) {
                    $this->widgetData[$widgetId] = DashboardWidgetService::getWidgetData($widgetId);
                }
                break;
            }
        }
        unset($widget);

        $this->saveLayout();
    }

    public function addWidget(string $widgetId): void
    {
        if (! isset($this->availableWidgets[$widgetId])) {
            return;
        }

        $existing = collect($this->layout)->firstWhere('id', $widgetId);
        if ($existing) {
            $this->toggleWidget($widgetId);

            return;
        }

        $this->layout[] = [
            'id' => $widgetId,
            'visible' => true,
            'position' => (int) (collect($this->layout)->max('position') ?? 0) + 1,
            'size' => $this->availableWidgets[$widgetId]['size'] ?? 'medium',
        ];

        $this->widgetData[$widgetId] = DashboardWidgetService::getWidgetData($widgetId);
        $this->saveLayout();
    }

    public function reorderWidgets(array $order): void
    {
        foreach ($order as $position => $widgetId) {
            foreach ($this->layout as &$widget) {
                if ($widget['id'] === $widgetId) {
                    $widget['position'] = $position;
                    break;
                }
            }
            unset($widget);
        }

        usort($this->layout, fn ($a, $b) => $a['position'] <=> $b['position']);
        $this->saveLayout();
    }

    public function resetLayout(): void
    {
        $this->layout = DashboardWidgetService::getDefaultLayout();
        $this->saveLayout();
        $this->loadWidgetData();
        $this->editMode = false;
        $this->dispatch('toast', type: 'success', message: 'Dashboard reset to default.');
    }

    private function saveLayout(): void
    {
        DashboardPreference::updateOrCreate(
            ['user_id' => auth()->id()],
            ['widget_layout' => array_values($this->layout)],
        );
    }

    public function refreshWidgets(): void
    {
        $this->loadWidgetData();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
