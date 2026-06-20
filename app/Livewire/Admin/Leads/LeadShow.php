<?php

namespace App\Livewire\Admin\Leads;

use App\Models\Lead;
use App\Models\PipelineStage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Lead')]
class LeadShow extends Component
{
    public Lead $lead;

    // Add-activity form.
    public string $activityType = 'note';
    public string $activityContent = '';

    // Mark-lost modal.
    public bool $confirmingLost = false;
    public ?string $lostReason = null;

    // Convert modal.
    public bool $confirmingConvert = false;

    public function mount(Lead $lead): void
    {
        $this->lead = $lead;
    }

    public function addActivity(): void
    {
        $validated = $this->validate([
            'activityType' => ['required', 'in:note,call,email,meeting,whatsapp'],
            'activityContent' => ['required', 'string', 'max:5000'],
        ]);

        $this->lead->logActivity($validated['activityType'], $validated['activityContent']);
        $this->lead->refresh();

        $this->reset('activityContent');
        $this->activityType = 'note';
        $this->dispatch('toast', type: 'success', message: 'Activity logged.');
    }

    public function moveStage(int $stageId): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('leads.edit'), 403);

        $newStage = PipelineStage::find($stageId);
        if (! $newStage || $newStage->id === $this->lead->stage_id) {
            return;
        }

        $oldStage = $this->lead->stage;
        $this->lead->update(['stage_id' => $newStage->id]);
        $this->lead->logActivity('stage_change', "Moved from {$oldStage->name} to {$newStage->name}");
        $this->lead->refresh();

        $this->dispatch('toast', type: 'success', message: "Moved to {$newStage->name}.");
    }

    public function convert()
    {
        abort_unless((bool) auth()->user()?->hasPermission('leads.convert'), 403);

        $client = $this->lead->convertToClient();
        $this->dispatch('toast', type: 'success', message: 'Lead converted to client!');

        return $this->redirect(route('admin.clients.show', $client), navigate: true);
    }

    public function markLost(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('leads.edit'), 403);

        $this->lead->markLost($this->lostReason);
        $this->lead->refresh();
        $this->confirmingLost = false;
        $this->lostReason = null;
        $this->dispatch('toast', type: 'success', message: 'Lead marked as lost.');
    }

    public function render()
    {
        $this->lead->load(['stage', 'convertedClient', 'activities']);

        return view('livewire.admin.leads.lead-show', [
            'stages' => PipelineStage::orderBy('sort_order')->get(),
        ]);
    }
}
