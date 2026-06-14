<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TicketAttachmentController extends Controller
{
    public function download(Ticket $ticket, TicketAttachment $attachment): BinaryFileResponse
    {
        abort_unless($attachment->ticket_id === $ticket->id, 404);
        abort_unless(Storage::disk('public')->exists($attachment->path), 404);

        return Storage::disk('public')->download($attachment->path, $attachment->filename);
    }
}
