<?php

namespace Tests\Feature;

use App\Livewire\Admin\AttachmentsManager;
use App\Livewire\Admin\NotesManager;
use App\Models\Attachment;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class NotesAttachmentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'a@x.com', 'password' => Hash::make('password123'),
            'role' => 'super_admin', 'is_active' => true,
        ]);
    }

    private function invoice(): Invoice
    {
        $client = Client::create(['name' => 'Acme', 'email' => 'acme@x.com', 'password' => Hash::make('password123')]);

        return Invoice::create([
            'invoice_number' => 'INV-001', 'client_id' => $client->id, 'status' => 'draft',
            'issue_date' => today(), 'subtotal' => 0, 'tax_rate' => 0, 'tax_amount' => 0,
            'discount_amount' => 0, 'total' => 0,
        ]);
    }

    public function test_admin_adds_internal_and_client_notes(): void
    {
        $invoice = $this->invoice();

        Livewire::actingAs($this->admin())->test(NotesManager::class, [
            'modelType' => Invoice::class, 'modelId' => $invoice->id,
        ])
            ->set('isInternal', true)->set('newNote', 'Internal only')->call('addNote')
            ->set('isInternal', false)->set('newNote', 'Hello client')->call('addNote')
            ->assertHasNoErrors();

        $this->assertSame(2, $invoice->notes()->count());
        $this->assertSame(1, $invoice->internalNotes()->count());
        $this->assertSame(1, $invoice->clientNotes()->count());
        $this->assertSame('Hello client', $invoice->clientNotes()->first()->content);
    }

    public function test_empty_note_is_rejected(): void
    {
        $invoice = $this->invoice();

        Livewire::actingAs($this->admin())->test(NotesManager::class, [
            'modelType' => Invoice::class, 'modelId' => $invoice->id,
        ])->set('newNote', '')->call('addNote')->assertHasErrors('newNote');

        $this->assertSame(0, $invoice->notes()->count());
    }

    public function test_delete_note_soft_deletes(): void
    {
        $invoice = $this->invoice();
        $note = Note::create([
            'notable_type' => Invoice::class, 'notable_id' => $invoice->id,
            'content' => 'x', 'is_internal' => true, 'author_type' => 'admin', 'author_name' => 'Admin',
        ]);

        Livewire::actingAs($this->admin())->test(NotesManager::class, [
            'modelType' => Invoice::class, 'modelId' => $invoice->id,
        ])->call('deleteNote', $note->id);

        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    public function test_upload_and_delete_attachment(): void
    {
        Storage::fake('public');
        $invoice = $this->invoice();

        $component = Livewire::actingAs($this->admin())->test(AttachmentsManager::class, [
            'modelType' => Invoice::class, 'modelId' => $invoice->id,
        ])
            ->set('uploadedFiles', [UploadedFile::fake()->image('photo.jpg', 100, 100)])
            ->call('uploadFiles')
            ->assertHasNoErrors();

        $this->assertSame(1, $invoice->attachments()->count());
        $attachment = $invoice->attachments()->first();
        Storage::disk('public')->assertExists($attachment->path);
        $this->assertTrue($attachment->is_image);

        $component->call('deleteAttachment', $attachment->id);
        $this->assertSame(0, $invoice->fresh()->attachments()->count());
        Storage::disk('public')->assertMissing($attachment->path);
    }

    public function test_file_size_formatting(): void
    {
        $a = new Attachment(['size' => 500]);
        $b = new Attachment(['size' => 2048]);
        $c = new Attachment(['size' => 3145728]);

        $this->assertSame('500 B', $a->file_size_formatted);
        $this->assertSame('2 KB', $b->file_size_formatted);
        $this->assertSame('3 MB', $c->file_size_formatted);
    }
}
