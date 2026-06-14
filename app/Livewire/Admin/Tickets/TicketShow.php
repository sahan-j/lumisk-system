<?php

namespace App\Livewire\Admin\Tickets;

use App\Mail\TicketReplyMail;
use App\Mail\TicketStatusMail;
use App\Models\Company;
use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Ticket')]
class TicketShow extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public string $replyMessage = '';
    public bool $isInternalNote = false;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $attachments = [];

    public function mount(Ticket $ticket): void
    {
        // First admin view of a brand-new ticket moves it into progress.
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        $this->ticket = $ticket;
    }

    public function reply(): void
    {
        $this->validate([
            'replyMessage' => ['required', 'string'],
            'isInternalNote' => ['boolean'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,zip'],
        ]);

        $company = Company::settings();

        $message = $this->ticket->messages()->create([
            'sender_type' => 'admin',
            'sender_name' => $company->name ?: 'Support',
            'message' => $this->replyMessage,
            'is_internal_note' => $this->isInternalNote,
        ]);

        $this->storeAttachments($message->id);

        if (! $this->isInternalNote) {
            $updates = ['status' => 'waiting_client'];
            if (! $this->ticket->first_response_at) {
                $updates['first_response_at'] = now();
            }
            $this->ticket->update($updates);

            if ($company->ticket_notifications_enabled && $this->ticket->client?->email) {
                try {
                    Mail::to($this->ticket->client->email)
                        ->send(new TicketReplyMail($this->ticket, $company, $this->replyMessage));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        $this->reset('replyMessage', 'isInternalNote', 'attachments');
        $this->ticket->refresh();
        $this->dispatch('toast', type: 'success', message: $this->isInternalNote ? 'Note added.' : 'Reply sent.');
    }

    public function updateStatus(string $status): void
    {
        if (! in_array($status, Ticket::STATUSES, true)) {
            return;
        }

        $updates = ['status' => $status];
        if ($status === 'resolved' && ! $this->ticket->resolved_at) {
            $updates['resolved_at'] = now();
        }
        if ($status === 'closed' && ! $this->ticket->closed_at) {
            $updates['closed_at'] = now();
        }

        $this->ticket->update($updates);

        $company = Company::settings();
        if ($company->ticket_notifications_enabled && $this->ticket->client?->email) {
            try {
                Mail::to($this->ticket->client->email)->send(new TicketStatusMail($this->ticket, $company));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->ticket->refresh();
        $this->dispatch('toast', type: 'success', message: 'Status updated.');
    }

    public function updatePriority(string $priority): void
    {
        if (! in_array($priority, Ticket::PRIORITIES, true)) {
            return;
        }

        // Recalculate the SLA deadline relative to ticket creation.
        $hours = Ticket::getSlaHours($priority);
        $this->ticket->update([
            'priority' => $priority,
            'sla_due_at' => $this->ticket->created_at->copy()->addHours($hours),
            'is_overdue_sla' => false,
        ]);

        $this->ticket->refresh();
        $this->dispatch('toast', type: 'success', message: 'Priority updated.');
    }

    private function storeAttachments(int $messageId): void
    {
        foreach ($this->attachments as $file) {
            $path = $file->store('tickets', 'public');
            $this->ticket->attachments()->create([
                'ticket_message_id' => $messageId,
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }
    }

    public function render()
    {
        $this->ticket->load(['client', 'project.tasks', 'messages.attachments']);

        return view('livewire.admin.tickets.ticket-show');
    }
}
