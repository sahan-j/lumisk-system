<?php

namespace Tests\Feature;

use App\Livewire\Admin\WhatsappModal;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\PublicToken;
use App\Services\DocumentNumberService;
use App\Services\PublicTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WhatsappPublicLinkTest extends TestCase
{
    use RefreshDatabase;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
        $this->client = Client::create([
            'name' => 'Acme Ltd', 'email' => 'hi@acme.com', 'phone' => '0773243784', 'portal_enabled' => false,
        ]);
    }

    private function makeInvoice(): Invoice
    {
        return Invoice::create([
            'invoice_number' => DocumentNumberService::nextInvoiceNumber(),
            'client_id' => $this->client->id, 'status' => 'sent',
            'issue_date' => now(), 'due_date' => now()->addDays(14),
            'subtotal' => 1000, 'total' => 1000,
        ]);
    }

    public function test_token_service_reuses_existing_valid_token(): void
    {
        $invoice = $this->makeInvoice();
        $service = app(PublicTokenService::class);

        $first = $service->getOrCreate('invoice', $invoice->id);
        $second = $service->getOrCreate('invoice', $invoice->id);

        $this->assertSame($first, $second);
        $this->assertSame(1, PublicToken::where('type', 'invoice')->where('reference_id', $invoice->id)->count());
    }

    public function test_public_view_is_accessible_without_auth(): void
    {
        $invoice = $this->makeInvoice();
        $token = app(PublicTokenService::class)->getOrCreate('invoice', $invoice->id);

        $this->get(route('public.view', $token))
            ->assertOk()
            ->assertSee($invoice->invoice_number);

        $this->assertSame(1, PublicToken::where('token', $token)->first()->access_count);
    }

    public function test_invalid_token_returns_404(): void
    {
        $this->get(route('public.view', 'nonexistent-token'))->assertNotFound();
    }

    public function test_expired_token_returns_404(): void
    {
        $invoice = $this->makeInvoice();
        PublicToken::create([
            'token' => 'expired-token-123',
            'type' => 'invoice',
            'reference_id' => $invoice->id,
            'expires_at' => now()->subDay(),
        ]);

        $this->get(route('public.view', 'expired-token-123'))->assertNotFound();
    }

    public function test_download_returns_pdf(): void
    {
        $invoice = $this->makeInvoice();
        $token = app(PublicTokenService::class)->getOrCreate('invoice', $invoice->id);

        $response = $this->get(route('public.view', $token) . '?download=1');
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_modal_cleans_phone_and_fills_template(): void
    {
        $invoice = $this->makeInvoice();

        $component = Livewire::test(WhatsappModal::class)
            ->call('handleOpen', 'invoice', $invoice->id)
            ->assertSet('show', true)
            ->assertSet('phone', '94773243784'); // 0773243784 → 94…

        $this->assertStringContainsString($invoice->invoice_number, $component->get('message'));
        $this->assertStringContainsString('Acme Ltd', $component->get('message'));
        $this->assertStringContainsString('https://wa.me/94773243784?text=', $component->get('whatsappUrl'));
    }
}
