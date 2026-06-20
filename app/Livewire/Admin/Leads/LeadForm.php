<?php

namespace App\Livewire\Admin\Leads;

use App\Models\ActivityLog;
use App\Models\Lead;
use App\Models\PipelineStage;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Lead')]
class LeadForm extends Component
{
    public ?Lead $lead = null;

    public string $name = '';
    public ?string $company_name = null;
    public ?string $email = null;
    public ?string $phone = null;
    public string $source = 'website';
    public ?int $stage_id = null;
    public ?float $value = null;
    public ?int $probability = 50;
    public ?string $expected_close_date = null;
    public ?string $assigned_to = null;
    public ?string $notes = null;

    public function mount(?Lead $lead = null): void
    {
        if ($lead && $lead->exists) {
            $this->lead = $lead;
            $this->name = $lead->name;
            $this->company_name = $lead->company_name;
            $this->email = $lead->email;
            $this->phone = $lead->phone;
            $this->source = $lead->source;
            $this->stage_id = $lead->stage_id;
            $this->value = $lead->value !== null ? (float) $lead->value : null;
            $this->probability = $lead->probability;
            $this->expected_close_date = $lead->expected_close_date?->format('Y-m-d');
            $this->assigned_to = $lead->assigned_to;
            $this->notes = $lead->notes;
        } else {
            $this->stage_id = PipelineStage::orderBy('sort_order')->value('id');
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'source' => ['required', 'in:' . implode(',', Lead::SOURCES)],
            'stage_id' => ['required', 'exists:pipeline_stages,id'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function save()
    {
        abort_unless((bool) auth()->user()?->hasPermission($this->lead ? 'leads.edit' : 'leads.create'), 403);

        $validated = $this->validate();
        $validated['probability'] = $validated['probability'] ?? 50;
        $validated['currency'] = company_settings()->currency ?: 'LKR';

        if ($this->lead) {
            $this->lead->update($validated);
            $lead = $this->lead;
            $this->dispatch('toast', type: 'success', message: 'Lead updated.');
        } else {
            // Place new leads at the bottom of their stage column.
            $validated['sort_order'] = (int) (Lead::where('stage_id', $validated['stage_id'])->max('sort_order') ?? 0) + 1;
            $validated['last_activity_at'] = now();

            $lead = Lead::create($validated);
            $lead->logActivity('note', 'Lead created.');

            ActivityLog::log('lead_created',
                "Lead '{$lead->name}' created",
                ['subject_type' => 'Lead', 'subject_id' => $lead->id, 'subject_label' => $lead->name]);

            $this->dispatch('toast', type: 'success', message: 'Lead created.');
        }

        return $this->redirect(route('admin.leads.show', $lead), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.leads.lead-form', [
            'stages' => PipelineStage::orderBy('sort_order')->get(),
            'sources' => Lead::SOURCES,
            'staff' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
