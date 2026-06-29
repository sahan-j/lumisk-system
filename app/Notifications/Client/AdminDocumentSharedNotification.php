<?php

namespace App\Notifications\Client;

use App\Models\ClientDocument;
use App\Notifications\BaseDatabaseNotification;

class AdminDocumentSharedNotification extends BaseDatabaseNotification
{
    public function __construct(public ClientDocument $document) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Document Shared',
            'message' => "We shared a document with you: {$this->document->title}",
            'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',
            'color' => '#10b981',
            'url' => route('portal.documents.index'),
            'type' => 'admin_document',
        ];
    }
}
