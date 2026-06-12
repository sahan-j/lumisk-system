<?php

namespace Tests\Feature;

use App\Livewire\Portal\Estimates\EstimateShow;
use App\Models\Client;
use App\Models\Company;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Client $client;
    protected Invoice $invoice;
    protected Estimate $estimate;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@lumisktechnology.com', 'password' => Hash::make('admin123'),
        ]);

        $this->client = Client::create([
            'name' => 'Acme Co', 'email' => 'acme@example.com',
            'password' => Hash::make('secret123'), 'portal_enabled' => true,
        ]);

        $this->invoice = Invoice::create([
            'invoice_number' => 'INV-001', 'client_id' => $this->client->id, 'status' => 'sent',
            'issue_date' => now(), 'due_date' => now()->addDays(14),
            'subtotal' => 1000, 'tax_rate' => 0, 'tax_amount' => 0, 'discount_amount' => 0, 'total' => 1000,
        ]);
        $this->invoice->items()->create(['name' => 'Work', 'quantity' => 1, 'unit_price' => 1000, 'total' => 1000, 'order' => 0]);

        $this->estimate = Estimate::create([
            'estimate_number' => 'EST-001', 'client_id' => $this->client->id, 'status' => 'sent',
            'issue_date' => now(), 'expiry_date' => now()->addDays(30),
            'subtotal' => 500, 'tax_rate' => 0, 'tax_amount' => 0, 'discount_amount' => 0, 'total' => 500,
        ]);
        $this->estimate->items()->create(['name' => 'Quote', 'quantity' => 1, 'unit_price' => 500, 'total' => 500, 'order' => 0]);
    }

    public function test_all_admin_pages_load(): void
    {
        $this->actingAs($this->admin);

        $urls = [
            '/admin/dashboard',
            '/admin/clients',
            "/admin/clients/{$this->client->id}",
            '/admin/invoices',
            '/admin/invoices/create',
            "/admin/invoices/{$this->invoice->id}",
            "/admin/invoices/{$this->invoice->id}/edit",
            '/admin/estimates',
            '/admin/estimates/create',
            "/admin/estimates/{$this->estimate->id}",
            "/admin/estimates/{$this->estimate->id}/edit",
            '/admin/saved-items',
            '/admin/settings',
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_admin_pdf_downloads(): void
    {
        $this->actingAs($this->admin);

        $inv = $this->get("/admin/invoices/{$this->invoice->id}/pdf");
        $inv->assertOk();
        $this->assertStringContainsString('application/pdf', $inv->headers->get('content-type'));

        $est = $this->get("/admin/estimates/{$this->estimate->id}/pdf");
        $est->assertOk();
        $this->assertStringContainsString('application/pdf', $est->headers->get('content-type'));
    }

    public function test_all_portal_pages_load(): void
    {
        $this->actingAs($this->client, 'client');

        foreach ([
            '/portal/dashboard',
            '/portal/invoices',
            "/portal/invoices/{$this->invoice->id}",
            '/portal/estimates',
            "/portal/estimates/{$this->estimate->id}",
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_portal_pdf_downloads(): void
    {
        $this->actingAs($this->client, 'client');
        $this->get("/portal/invoices/{$this->invoice->id}/pdf")->assertOk();
        $this->get("/portal/estimates/{$this->estimate->id}/pdf")->assertOk();
    }

    public function test_client_cannot_access_other_clients_documents(): void
    {
        $other = Client::create([
            'name' => 'Other', 'email' => 'other@example.com',
            'password' => Hash::make('secret123'), 'portal_enabled' => true,
        ]);

        $this->actingAs($other, 'client');
        $this->get("/portal/invoices/{$this->invoice->id}")->assertForbidden();
        $this->get("/portal/estimates/{$this->estimate->id}")->assertForbidden();
        $this->get("/portal/invoices/{$this->invoice->id}/pdf")->assertForbidden();
    }

    public function test_client_can_accept_estimate_with_note(): void
    {
        $this->actingAs($this->client, 'client');

        Livewire::test(EstimateShow::class, ['estimate' => $this->estimate])
            ->call('openResponse', 'accepted')
            ->set('client_note', 'Looks great, approved.')
            ->call('submitResponse');

        $this->estimate->refresh();
        $this->assertSame('accepted', $this->estimate->status);
        $this->assertSame('Looks great, approved.', $this->estimate->client_note);
    }

    public function test_client_cannot_respond_to_non_sent_estimate(): void
    {
        $this->estimate->update(['status' => 'accepted']);
        $this->actingAs($this->client, 'client');

        Livewire::test(EstimateShow::class, ['estimate' => $this->estimate])
            ->call('openResponse', 'rejected')
            ->assertSet('showResponse', false);
    }
}
