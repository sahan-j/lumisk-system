<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Estimate;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuoteRequestTest extends TestCase
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

    public function test_client_can_submit_a_quote_request(): void
    {
        Storage::fake('private');
        Notification::fake();
        User::factory()->create(); // admin to notify
        $client = $this->client();

        $response = $this->actingAs($client, 'client')->post(route('portal.quote-requests.store'), [
            'title'        => 'Restaurant Website',
            'description'  => 'We need a restaurant website with an online menu and reservations.',
            'service_type' => 'website',
            'budget_range' => '50k_150k',
            'timeline'     => 'asap',
            'attachments'  => [UploadedFile::fake()->create('brief.pdf', 100, 'application/pdf')],
        ]);

        $quoteRequest = QuoteRequest::first();
        $this->assertNotNull($quoteRequest);
        $response->assertRedirect(route('portal.quote-requests.show', $quoteRequest));

        $this->assertSame('pending', $quoteRequest->status);
        $this->assertSame($client->id, $quoteRequest->client_id);
        $this->assertStringStartsWith('QR-', $quoteRequest->request_number);
        $this->assertCount(1, $quoteRequest->attachments);
        Storage::disk('private')->assertExists($quoteRequest->attachments[0]['path']);

        $this->assertDatabaseHas('activity_logs', ['type' => 'quote_request_created', 'client_id' => $client->id]);
    }

    public function test_store_validates_required_fields(): void
    {
        $client = $this->client();

        $this->actingAs($client, 'client')
            ->post(route('portal.quote-requests.store'), ['description' => 'too short'])
            ->assertSessionHasErrors(['title', 'description', 'service_type', 'budget_range', 'timeline']);
    }

    public function test_client_cannot_view_another_clients_request(): void
    {
        $client = $this->client();
        $other = Client::create(['name' => 'Other', 'email' => 'other@example.com', 'portal_enabled' => true]);
        $quoteRequest = QuoteRequest::create([
            'request_number' => 'QR-001', 'client_id' => $client->id,
            'title' => 'X', 'description' => str_repeat('a', 20),
            'service_type' => 'website', 'budget_range' => 'flexible', 'timeline' => 'flexible',
        ]);

        $this->actingAs($other, 'client')
            ->get(route('portal.quote-requests.show', $quoteRequest))
            ->assertForbidden();
    }

    public function test_admin_opening_pending_request_marks_it_reviewing(): void
    {
        Notification::fake();
        $client = $this->client();
        $admin = User::factory()->create();
        $quoteRequest = QuoteRequest::create([
            'request_number' => 'QR-001', 'client_id' => $client->id,
            'title' => 'X', 'description' => str_repeat('a', 20),
            'service_type' => 'website', 'budget_range' => 'flexible', 'timeline' => 'flexible',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.quote-requests.show', $quoteRequest))
            ->assertOk();

        $this->assertSame('reviewing', $quoteRequest->fresh()->status);
    }

    public function test_admin_can_convert_request_to_estimate(): void
    {
        Notification::fake();
        $client = $this->client();
        $admin = User::factory()->create();
        $quoteRequest = QuoteRequest::create([
            'request_number' => 'QR-001', 'client_id' => $client->id,
            'title' => 'Landing Page', 'description' => str_repeat('a', 20),
            'service_type' => 'website', 'budget_range' => 'flexible', 'timeline' => 'flexible',
            'status' => 'reviewing',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.quote-requests.convert', $quoteRequest), [
            'note' => 'Standard package',
        ]);

        $estimate = Estimate::first();
        $this->assertNotNull($estimate);
        $response->assertRedirect(route('admin.estimates.edit', $estimate));

        $quoteRequest->refresh();
        $this->assertSame('converted', $quoteRequest->status);
        $this->assertSame($estimate->id, $quoteRequest->converted_estimate_id);
        $this->assertSame('draft', $estimate->status);
        $this->assertCount(1, $estimate->items);
        $this->assertSame('Landing Page', $estimate->items->first()->name);
    }

    public function test_admin_can_decline_request(): void
    {
        Notification::fake();
        $client = $this->client();
        $admin = User::factory()->create();
        $quoteRequest = QuoteRequest::create([
            'request_number' => 'QR-001', 'client_id' => $client->id,
            'title' => 'X', 'description' => str_repeat('a', 20),
            'service_type' => 'website', 'budget_range' => 'flexible', 'timeline' => 'flexible',
            'status' => 'reviewing',
        ]);

        $this->actingAs($admin)->post(route('admin.quote-requests.decline', $quoteRequest), [
            'declined_reason' => 'Outside our current service scope right now.',
        ])->assertRedirect(route('admin.quote-requests.show', $quoteRequest));

        $quoteRequest->refresh();
        $this->assertSame('declined', $quoteRequest->status);
        $this->assertNotNull($quoteRequest->declined_reason);
    }
}
