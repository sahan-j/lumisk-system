<?php

namespace App\Livewire\Portal\Tickets;

use App\Mail\TicketCreatedAdminMail;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.portal')]
#[Title('New Support Ticket')]
class TicketCreate extends Component
{
    use WithFileUploads;

    public string $subject = '';
    public string $type = 'general_question';
    public string $priority = 'medium';
    public ?int $project_id = null;
    public string $message = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $attachments = [];

    public function store()
    {
        $client = Auth::guard('client')->user();

        $this->validate([
            'subject' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:' . implode(',', Ticket::TYPES)],
            'priority' => ['required', 'in:' . implode(',', Ticket::PRIORITIES)],
            'project_id' => ['nullable', 'exists:projects,id'],
            'message' => ['required', 'string'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,zip'],
        ]);

        // Guard: a chosen project must belong to this client.
        if ($this->project_id && ! $client->projects()->whereKey($this->project_id)->exists()) {
            $this->project_id = null;
        }

        $hours = Ticket::getSlaHours($this->priority);

        $ticket = Ticket::create([
            'ticket_number' => Ticket::generateNumber(),
            'subject' => $this->subject,
            'type' => $this->type,
            'status' => 'open',
            'priority' => $this->priority,
            'client_id' => $client->id,
            'project_id' => $this->project_id,
            'sla_due_at' => now()->addHours($hours),
        ]);

        $message = $ticket->messages()->create([
            'sender_type' => 'client',
            'sender_name' => $client->name,
            'message' => $this->message,
            'is_internal_note' => false,
        ]);

        foreach ($this->attachments as $file) {
            // Private disk: ticket files are served only via the ownership-checked download controller.
            $path = $file->store('tickets', 'private');
            $ticket->attachments()->create([
                'ticket_message_id' => $message->id,
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }

        ActivityLog::log('ticket_created',
            "Ticket {$ticket->ticket_number} opened by {$client->name}",
            ['subject_type' => 'Ticket', 'subject_id' => $ticket->id,
             'subject_label' => $ticket->ticket_number,
             'causer_type' => 'client', 'causer_name' => $client->name, 'client_id' => $client->id]);

        $company = Company::settings();
        if ($company->ticket_notifications_enabled && $company->email) {
            try {
                Mail::to($company->email)->send(new TicketCreatedAdminMail($ticket, $company, $this->message));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        \App\Models\User::all()->each(fn ($admin) => $admin->notify(new \App\Notifications\Admin\NewTicketNotification($ticket)));

        $this->dispatch('toast', type: 'success', message: "Ticket {$ticket->ticket_number} created.");

        return $this->redirect(route('portal.tickets.show', $ticket), navigate: true);
    }

    public function render()
    {
        $projects = Project::where('client_id', Auth::guard('client')->id())
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.portal.tickets.ticket-create', [
            'projects' => $projects,
            'types' => Ticket::TYPES,
            'priorities' => Ticket::PRIORITIES,
        ]);
    }
}
