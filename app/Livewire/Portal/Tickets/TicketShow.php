<?php

namespace App\Livewire\Portal\Tickets;

use App\Mail\TicketReplyAdminMail;
use App\Models\Company;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Layout('components.layouts.portal')]
#[Title('Ticket')]
class TicketShow extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public string $replyMessage = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $attachments = [];

    public function mount(Ticket $ticket): void
    {
        if ($ticket->client_id !== Auth::guard('client')->id()) {
            throw new NotFoundHttpException();
        }

        $this->ticket = $ticket;
    }

    public function reply(): void
    {
        abort_unless($this->ticket->client_id === Auth::guard('client')->id(), 403);

        if (in_array($this->ticket->status, ['closed', 'resolved'], true)) {
            return;
        }

        $this->validate([
            'replyMessage' => ['required', 'string'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,zip'],
        ]);

        $client = Auth::guard('client')->user();

        $message = $this->ticket->messages()->create([
            'sender_type' => 'client',
            'sender_name' => $client->name,
            'message' => $this->replyMessage,
            'is_internal_note' => false,
        ]);

        foreach ($this->attachments as $file) {
            $path = $file->store('tickets', 'public');
            $this->ticket->attachments()->create([
                'ticket_message_id' => $message->id,
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }

        // A client reply re-opens the conversation for the team.
        $this->ticket->update(['status' => 'open']);

        $company = Company::settings();
        if ($company->ticket_notifications_enabled && $company->email) {
            try {
                Mail::to($company->email)->send(new TicketReplyAdminMail($this->ticket, $company, $this->replyMessage));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->reset('replyMessage', 'attachments');
        $this->ticket->refresh();
        $this->dispatch('toast', type: 'success', message: 'Reply sent.');
    }

    public function close(): void
    {
        abort_unless($this->ticket->client_id === Auth::guard('client')->id(), 403);

        $this->ticket->update(['status' => 'closed', 'closed_at' => now()]);
        $this->ticket->refresh();
        $this->dispatch('toast', type: 'success', message: 'Ticket closed.');
    }

    public function reopen(): void
    {
        abort_unless($this->ticket->client_id === Auth::guard('client')->id(), 403);

        if (in_array($this->ticket->status, ['closed', 'resolved'], true)) {
            $this->ticket->update(['status' => 'open', 'closed_at' => null, 'resolved_at' => null]);
            $this->ticket->refresh();
            $this->dispatch('toast', type: 'success', message: 'Ticket reopened.');
        }
    }

    public function render()
    {
        // Internal notes are never exposed to the client.
        $this->ticket->load([
            'messages' => fn ($q) => $q->where('is_internal_note', false),
            'messages.attachments',
        ]);

        return view('livewire.portal.tickets.ticket-show');
    }
}
