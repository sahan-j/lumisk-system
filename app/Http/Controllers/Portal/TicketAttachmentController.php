<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TicketAttachmentController extends Controller
{
    public function download(Ticket $ticket, TicketAttachment $attachment): BinaryFileResponse
    {
        // Clients may only download attachments on their own tickets.
        abort_unless($ticket->client_id === Auth::guard('client')->id(), 403);
        abort_unless($attachment->ticket_id === $ticket->id, 404);

        // Internal-note attachments must never be exposed to clients.
        abort_if($attachment->message && $attachment->message->is_internal_note, 403);
        abort_unless(Storage::disk('public')->exists($attachment->path), 404);

        return Storage::disk('public')->download($attachment->path, $attachment->filename);
    }
}
