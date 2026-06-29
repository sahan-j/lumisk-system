<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            'name'           => 'Acme',
            'email'          => 'acme@example.com',
            'portal_enabled' => true,
            'password'       => bcrypt('secret123'),
        ]);
    }

    public function test_client_can_upload_a_document(): void
    {
        Storage::fake('private');
        User::factory()->create(); // an admin to receive the notification
        $client = $this->client();

        $response = $this->actingAs($client, 'client')->post(route('portal.documents.upload'), [
            'files'        => [UploadedFile::fake()->create('receipt.pdf', 120, 'application/pdf')],
            'category'     => 'payment_proof',
            'client_note'  => 'My receipt',
        ]);

        $response->assertRedirect(route('portal.documents.index'));

        $this->assertDatabaseHas('client_documents', [
            'client_id'         => $client->id,
            'uploaded_by'       => 'client',
            'category'          => 'payment_proof',
            'original_filename' => 'receipt.pdf',
        ]);

        $doc = $client->documents()->first();
        Storage::disk('private')->assertExists($doc->path);
        $this->assertSame(1, (int) $client->fresh()->unread_documents_count);
    }

    public function test_upload_requires_a_file_and_category(): void
    {
        Storage::fake('private');
        $client = $this->client();

        $response = $this->actingAs($client, 'client')->post(route('portal.documents.upload'), [
            'category' => '',
        ]);

        $response->assertSessionHasErrors(['files', 'category']);
    }
}
