<?php

namespace App\Livewire\Admin\TimeTracking;

use App\Models\Client;
use App\Models\Project;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Time Tracking')]
class TimeTrackingIndex extends Component
{
    public string $filterDate = '';

    // Start-timer form
    public string $description = '';
    public ?int $projectId = null;
    public ?int $clientId = null;
    public bool $isBillable = true;

    // Manual-entry form
    public bool $showManual = false;
    public string $mDescription = '';
    public ?int $mProjectId = null;
    public ?int $mClientId = null;
    public string $mDate = '';
    public string $mStart = '';
    public string $mEnd = '';
    public ?float $mRate = null;
    public bool $mBillable = true;

    public function mount(): void
    {
        $this->filterDate = today()->format('Y-m-d');
        $this->mDate = today()->format('Y-m-d');
    }

    /** Resolve the billing rate from the client, falling back to the staff member's rate. */
    private function resolveRate(?int $clientId): ?float
    {
        $rate = null;
        if ($clientId) {
            $rate = Client::find($clientId)?->default_hourly_rate;
        }

        return $rate ?? auth()->user()->hourly_rate;
    }

    public function startTimer(): void
    {
        $this->validate([
            'description' => ['nullable', 'string', 'max:500'],
            'projectId' => ['nullable', 'exists:projects,id'],
            'clientId' => ['nullable', 'exists:clients,id'],
        ]);

        // Auto-stop any running timer for this user.
        TimeEntry::where('user_id', auth()->id())
            ->whereNull('ended_at')
            ->get()
            ->each(function ($entry) {
                $entry->update([
                    'ended_at' => now(),
                    'duration_minutes' => TimeEntry::calculateDuration($entry->started_at, now()),
                ]);
            });

        TimeEntry::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'description' => $this->description ?: null,
            'project_id' => $this->projectId,
            'client_id' => $this->clientId,
            'started_at' => now(),
            'date' => today(),
            'hourly_rate' => $this->resolveRate($this->clientId),
            'is_billable' => $this->isBillable,
        ]);

        $this->reset('description', 'projectId', 'clientId');
        $this->isBillable = true;
        $this->dispatch('toast', type: 'success', message: 'Timer started.');
    }

    public function stopTimer(int $id): void
    {
        $entry = TimeEntry::findOrFail($id);
        abort_unless($entry->user_id === auth()->id(), 403);

        if ($entry->ended_at) {
            return;
        }

        $entry->update([
            'ended_at' => now(),
            'duration_minutes' => TimeEntry::calculateDuration($entry->started_at, now()),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Timer stopped: ' . $entry->fresh()->duration_formatted);
    }

    public function addManual(): void
    {
        $validated = $this->validate([
            'mDescription' => ['nullable', 'string', 'max:500'],
            'mProjectId' => ['nullable', 'exists:projects,id'],
            'mClientId' => ['nullable', 'exists:clients,id'],
            'mDate' => ['required', 'date'],
            'mStart' => ['required', 'date_format:H:i'],
            'mEnd' => ['required', 'date_format:H:i', 'after:mStart'],
            'mRate' => ['nullable', 'numeric', 'min:0'],
        ], [
            'mEnd.after' => 'End time must be after the start time.',
        ]);

        $started = Carbon::parse($validated['mDate'] . ' ' . $validated['mStart']);
        $ended = Carbon::parse($validated['mDate'] . ' ' . $validated['mEnd']);

        TimeEntry::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'description' => $this->mDescription ?: null,
            'project_id' => $this->mProjectId,
            'client_id' => $this->mClientId,
            'started_at' => $started,
            'ended_at' => $ended,
            'duration_minutes' => TimeEntry::calculateDuration($started, $ended),
            'date' => $validated['mDate'],
            'hourly_rate' => $this->mRate ?? $this->resolveRate($this->mClientId),
            'is_billable' => $this->mBillable,
        ]);

        $this->reset('mDescription', 'mProjectId', 'mClientId', 'mStart', 'mEnd', 'mRate');
        $this->mDate = today()->format('Y-m-d');
        $this->mBillable = true;
        $this->showManual = false;
        $this->dispatch('toast', type: 'success', message: 'Time entry added.');
    }

    public function deleteEntry(int $id): void
    {
        $entry = TimeEntry::findOrFail($id);
        abort_unless($entry->user_id === auth()->id(), 403);

        $entry->delete();
        $this->dispatch('toast', type: 'success', message: 'Entry deleted.');
    }

    public function render()
    {
        $userId = auth()->id();

        $entries = TimeEntry::with(['project', 'client', 'task'])
            ->where('user_id', $userId)
            ->whereDate('date', $this->filterDate ?: today())
            ->orderByDesc('started_at')
            ->get();

        $runningEntry = TimeEntry::where('user_id', $userId)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        return view('livewire.admin.time-tracking.time-tracking-index', [
            'entries' => $entries,
            'runningEntry' => $runningEntry,
            'todayMinutes' => (int) TimeEntry::where('user_id', $userId)
                ->whereDate('date', today())->whereNotNull('duration_minutes')->sum('duration_minutes'),
            'todayBillable' => (int) TimeEntry::where('user_id', $userId)
                ->whereDate('date', today())->where('is_billable', true)
                ->whereNotNull('duration_minutes')->sum('duration_minutes'),
            'weekMinutes' => (int) TimeEntry::where('user_id', $userId)
                ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                ->whereNotNull('duration_minutes')->sum('duration_minutes'),
            'projects' => Project::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
