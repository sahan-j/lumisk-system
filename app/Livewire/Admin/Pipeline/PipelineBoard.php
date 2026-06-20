<?php

namespace App\Livewire\Admin\Pipeline;

use App\Models\ActivityLog;
use App\Models\Lead;
use App\Models\PipelineStage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Sales Pipeline')]
class PipelineBoard extends Component
{
    // Convert-to-client prompt (shown when a lead is dropped on the Won stage).
    public ?int $confirmConvertLeadId = null;
    public ?string $confirmConvertName = null;

    // Stage management modal.
    public bool $managingStages = false;
    public ?int $stageId = null;
    public string $stageName = '';
    public string $stageColor = '#6d5cff';
    public int $stageSortOrder = 0;
    public bool $stageIsWon = false;
    public bool $stageIsLost = false;

    /**
     * Persist a drag-drop move: change stage (logging the transition) and
     * re-order the cards within the target column.
     */
    public function moveLead(int $leadId, int $stageId, array $orderedIds = []): void
    {
        $lead = Lead::find($leadId);
        $newStage = PipelineStage::find($stageId);

        if (! $lead || ! $newStage) {
            return;
        }

        if ($lead->stage_id !== $newStage->id) {
            $oldStage = $lead->stage;
            $lead->update(['stage_id' => $newStage->id]);
            $lead->logActivity('stage_change', "Moved from {$oldStage->name} to {$newStage->name}");

            ActivityLog::log('lead_stage_changed',
                "Lead '{$lead->name}' moved to {$newStage->name}",
                ['subject_type' => 'Lead', 'subject_id' => $lead->id, 'subject_label' => $lead->name]);

            // Prompt to convert when dropped on the won stage.
            if ($newStage->is_won && ! $lead->is_converted) {
                $this->confirmConvertLeadId = $lead->id;
                $this->confirmConvertName = $lead->name;
            }
        }

        foreach ($orderedIds as $index => $id) {
            Lead::where('id', $id)->update(['sort_order' => $index]);
        }

        $this->dispatch('toast', type: 'success', message: 'Pipeline updated.');
    }

    public function convertConfirmed()
    {
        abort_unless((bool) auth()->user()?->hasPermission('leads.convert'), 403);

        $lead = Lead::find($this->confirmConvertLeadId);
        if (! $lead) {
            $this->confirmConvertLeadId = null;

            return null;
        }

        $client = $lead->convertToClient();
        $this->confirmConvertLeadId = null;
        $this->confirmConvertName = null;

        $this->dispatch('toast', type: 'success', message: 'Lead converted to client!');

        return $this->redirect(route('admin.clients.show', $client), navigate: true);
    }

    public function dismissConvert(): void
    {
        $this->confirmConvertLeadId = null;
        $this->confirmConvertName = null;
    }

    // ---- Stage management ----

    public function openStages(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('pipeline.manage_stages'), 403);
        $this->resetStageForm();
        $this->managingStages = true;
    }

    public function resetStageForm(): void
    {
        $this->stageId = null;
        $this->stageName = '';
        $this->stageColor = '#6d5cff';
        $this->stageSortOrder = (int) (PipelineStage::max('sort_order') ?? 0) + 1;
        $this->stageIsWon = false;
        $this->stageIsLost = false;
        $this->resetValidation();
    }

    public function editStage(int $id): void
    {
        $stage = PipelineStage::find($id);
        if (! $stage) {
            return;
        }
        $this->stageId = $stage->id;
        $this->stageName = $stage->name;
        $this->stageColor = $stage->color;
        $this->stageSortOrder = $stage->sort_order;
        $this->stageIsWon = $stage->is_won;
        $this->stageIsLost = $stage->is_lost;
    }

    public function saveStage(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('pipeline.manage_stages'), 403);

        $validated = $this->validate([
            'stageName' => ['required', 'string', 'max:255'],
            'stageColor' => ['required', 'string', 'max:20'],
            'stageSortOrder' => ['required', 'integer', 'min:0'],
            'stageIsWon' => ['boolean'],
            'stageIsLost' => ['boolean'],
        ]);

        PipelineStage::updateOrCreate(
            ['id' => $this->stageId],
            [
                'name' => $validated['stageName'],
                'color' => $validated['stageColor'],
                'sort_order' => $validated['stageSortOrder'],
                'is_won' => $this->stageIsWon,
                'is_lost' => $this->stageIsLost,
            ]
        );

        $this->resetStageForm();
        $this->dispatch('toast', type: 'success', message: 'Stage saved.');
    }

    public function deleteStage(int $id): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('pipeline.manage_stages'), 403);

        $stage = PipelineStage::withCount('leads')->find($id);
        if (! $stage) {
            return;
        }

        if ($stage->leads_count > 0) {
            $this->dispatch('toast', type: 'error', message: 'Cannot delete a stage that still has leads.');

            return;
        }

        $stage->delete();
        $this->dispatch('toast', type: 'success', message: 'Stage deleted.');
    }

    public function render()
    {
        $stages = PipelineStage::orderBy('sort_order')
            ->with(['leads' => fn ($q) => $q->whereNull('converted_to_client_id')->orderBy('sort_order')])
            ->get();

        // Active (non-lost, non-converted) leads drive the headline pipeline numbers.
        $activeLeads = Lead::whereNull('converted_to_client_id')
            ->whereHas('stage', fn ($q) => $q->where('is_lost', false))
            ->get();

        $wonThisMonth = Lead::whereNotNull('converted_at')
            ->whereMonth('converted_at', now()->month)
            ->whereYear('converted_at', now()->year)
            ->get();

        return view('livewire.admin.pipeline.pipeline-board', [
            'stages' => $stages,
            'stats' => [
                'total_leads' => $activeLeads->count(),
                'total_value' => $activeLeads->sum('value'),
                'weighted_value' => $activeLeads->sum('weighted_value'),
                'won_this_month' => $wonThisMonth->count(),
                'won_value_this_month' => $wonThisMonth->sum('value'),
            ],
        ]);
    }
}
