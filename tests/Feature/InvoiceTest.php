<?php

namespace Tests\Feature;

use App\Livewire\Admin\Invoices\InvoiceForm;
use App\Livewire\Admin\Invoices\InvoicesIndex;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
        $this->actingAs(User::create([
            'name' => 'Admin', 'email' => 'a@b.com', 'password' => bcrypt('x'),
        ]));
        $this->client = Client::create([
            'name' => 'Acme', 'email' => 'acme@example.com', 'portal_enabled' => false,
        ]);
    }

    public function test_creates_invoice_with_generated_number_and_totals(): void
    {
        Livewire::test(InvoiceForm::class)
            ->set('client_id', $this->client->id)
            ->set('items', [
                ['name' => 'Design', 'description' => '', 'quantity' => 2, 'unit_price' => 1000],
                ['name' => 'Hosting', 'description' => '', 'quantity' => 1, 'unit_price' => 500],
            ])
            ->set('tax_rate', 10)
            ->set('discount_amount', 250)
            ->call('save');

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        $this->assertSame('INV-001', $invoice->invoice_number);
        $this->assertEquals(2500, (float) $invoice->subtotal);   // 2000 + 500
        $this->assertEquals(250, (float) $invoice->tax_amount);  // 10% of 2500
        $this->assertEquals(2500, (float) $invoice->total);      // 2500 + 250 - 250
        $this->assertCount(2, $invoice->items);
        $this->assertSame(2, (int) Company::settings()->invoice_next_number);
    }

    public function test_numbers_increment_sequentially(): void
    {
        foreach (range(1, 3) as $_) {
            Livewire::test(InvoiceForm::class)
                ->set('client_id', $this->client->id)
                ->set('items', [['name' => 'X', 'description' => '', 'quantity' => 1, 'unit_price' => 100]])
                ->call('save');
        }

        $this->assertEquals(['INV-001', 'INV-002', 'INV-003'], Invoice::orderBy('id')->pluck('invoice_number')->toArray());
    }

    public function test_validation_requires_client_and_items(): void
    {
        Livewire::test(InvoiceForm::class)
            ->set('client_id', null)
            ->set('items', [['name' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0]])
            ->call('save')
            ->assertHasErrors(['client_id', 'items.0.name']);
    }

    public function test_duplicate_creates_new_draft(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-001', 'client_id' => $this->client->id, 'status' => 'paid',
            'issue_date' => now(), 'subtotal' => 100, 'total' => 100,
        ]);
        $invoice->items()->create(['name' => 'A', 'quantity' => 1, 'unit_price' => 100, 'total' => 100, 'order' => 0]);
        // Mirror real state: the counter has advanced past the existing number.
        Company::settings()->update(['invoice_next_number' => 2]);

        Livewire::test(InvoicesIndex::class)->call('duplicate', $invoice->id);

        $this->assertSame(2, Invoice::count());
        $copy = Invoice::where('id', '!=', $invoice->id)->first();
        $this->assertSame('draft', $copy->status);
        $this->assertCount(1, $copy->items);
    }
}
