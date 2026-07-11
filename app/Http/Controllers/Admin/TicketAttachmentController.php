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

        // New uploads live on the private disk; fall back to public for legacy files.
        $disk = Storage::disk('private')->exists($attachment->path) ? 'private' : 'public';
        abort_unless(Storage::disk($disk)->exists($attachment->path), 404);

        return Storage::disk($disk)->download($attachment->path, $attachment->filename);
    }
}
