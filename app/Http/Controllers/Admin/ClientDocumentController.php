<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientDocumentController extends Controller
{
    public function download(ClientDocument $document): StreamedResponse
    {
        abort_unless((bool) auth()->user()?->hasPermission('clients.view'), 403);

        if ($document->uploaded_by === 'client') {
            $document->update(['viewed_by_admin' => true]);
        }

        return Storage::disk('private')->download($document->path, $document->original_filename);
    }
}
