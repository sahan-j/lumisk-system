<?php

namespace App\Notifications\Admin;

use App\Models\Client;
use App\Notifications\BaseDatabaseNotification;

class ClientDocumentUploadedNotification extends BaseDatabaseNotification
{
    public function __construct(public Client $client, public string $title, public string $categoryLabel) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Document from Client',
            'message' => "{$this->client->name} uploaded: {$this->title} ({$this->categoryLabel})",
            'icon' => 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12',
            'color' => '#6d5cff',
            'url' => route('admin.clients.documents', $this->client),
            'type' => 'client_document',
        ];
    }
}
