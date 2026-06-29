<?php

namespace Tests\Feature;

use App\Livewire\Portal\Documents\DocumentsIndex;
use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ClientDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
    }

    private function client(): Client
    {
        return Client::create([
            'name' => 'Acme',
            'email' => 'acme@example.com',
            'portal_enabled' => true,
            'password' => bcrypt('secret123'),
        ]);
    }

    public function test_client_can_upload_a_document(): void
    {
        Storage::fake('private');
        User::factory()->create(); // an admin to receive the notification
        $client = $this->client();

        Livewire::actingAs($client, 'client')
            ->test(DocumentsIndex::class)
            ->set('files', [UploadedFile::fake()->create('receipt.pdf', 120, 'application/pdf')])
            ->set('category', 'payment_proof')
            ->set('clientNote', 'My receipt')
            ->call('upload')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('client_documents', [
            'client_id' => $client->id,
            'uploaded_by' => 'client',
            'category' => 'payment_proof',
            'original_filename' => 'receipt.pdf',
        ]);

        // File landed on the private disk, and the admin unread counter advanced.
        $doc = $client->documents()->first();
        Storage::disk('private')->assertExists($doc->path);
        $this->assertSame(1, (int) $client->fresh()->unread_documents_count);
    }

    public function test_upload_requires_a_file_and_category(): void
    {
        Storage::fake('private');
        $client = $this->client();

        Livewire::actingAs($client, 'client')
            ->test(DocumentsIndex::class)
            ->set('category', '')
            ->call('upload')
            ->assertHasErrors(['files', 'category']);
    }
}
