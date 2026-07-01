<?php

namespace Tests\Feature;

use App\Livewire\Admin\RecordPaymentModal;
use App\Livewire\Portal\Dashboard as PortalDashboard;
use App\Livewire\Portal\Invoices\InvoiceShow as PortalInvoiceShow;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentTest extends TestCase
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

    protected function makeInvoice(string $status = 'sent', float $total = 1000): Invoice
    {
        return Invoice::create([
            'invoice_number' => 'INV-001', 'client_id' => $this->client->id, 'status' => $status,
            'issue_date' => now(), 'subtotal' => $total, 'total' => $total,
        ]);
    }

    public function test_accessors_compute_paid_and_outstanding(): void
    {
        $invoice = $this->makeInvoice('sent', 1000);
        $invoice->payments()->create(['amount' => 300, 'payment_method' => 'cash', 'payment_date' => now()]);
        $invoice->load('payments');

        $this->assertEquals(300, $invoice->total_paid);
        $this->assertEquals(700, $invoice->outstanding_balance);
        $this->assertFalse($invoice->is_fully_paid);
    }

    public function test_partial_payment_keeps_invoice_sent(): void
    {
        $invoice = $this->makeInvoice('sent', 1000);

        Livewire::test(RecordPaymentModal::class)
            ->call('open', $invoice->id)
            ->assertSet('amount', 1000.0) // pre-filled with outstanding
            ->set('amount', 400)
            ->set('paymentMethod', 'bank_transfer')
            ->set('paymentDate', now()->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors();

        $invoice->refresh()->load('payments');
        $this->assertSame('sent', $invoice->status);
        $this->assertEquals(400, $invoice->total_paid);
        $this->assertEquals(600, $invoice->outstanding_balance);
    }

    public function test_full_payment_marks_invoice_paid(): void
    {
        $invoice = $this->makeInvoice('sent', 1000);

        Livewire::test(RecordPaymentModal::class)
            ->call('open', $invoice->id)
            ->set('amount', 1000)
            ->set('paymentDate', now()->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertTrue($invoice->load('payments')->is_fully_paid);
    }

    public function test_draft_invoice_becomes_sent_on_partial_payment(): void
    {
        $invoice = $this->makeInvoice('draft', 1000);

        Livewire::test(RecordPaymentModal::class)
            ->call('open', $invoice->id)
            ->set('amount', 250)
            ->set('paymentDate', now()->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('sent', $invoice->refresh()->status);
    }

    public function test_cannot_pay_more_than_outstanding(): void
    {
        $invoice = $this->makeInvoice('sent', 1000);

        Livewire::test(RecordPaymentModal::class)
            ->call('open', $invoice->id)
            ->set('amount', 1500)
            ->set('paymentDate', now()->format('Y-m-d'))
            ->call('save')
            ->assertHasErrors(['amount']);

        $this->assertSame(0, Payment::count());
    }

    public function test_deleting_payment_reverts_paid_to_sent(): void
    {
        $invoice = $this->makeInvoice('sent', 1000);
        $payment = $invoice->payments()->create(['amount' => 1000, 'payment_method' => 'cash', 'payment_date' => now()]);
        $invoice->update(['status' => 'paid']);

        Livewire::test(RecordPaymentModal::class)
            ->call('open', $invoice->id)
            ->call('deletePayment', $payment->id);

        $invoice->refresh();
        $this->assertSame('sent', $invoice->status);
        $this->assertSame(0, Payment::count());
    }

    public function test_client_portal_shows_recorded_payment(): void
    {
        $invoice = $this->makeInvoice('sent', 1000);
        $invoice->payments()->create([
            'amount' => 400, 'payment_method' => 'bank_transfer',
            'payment_date' => now(), 'reference_number' => 'TXN-99',
        ]);

        Livewire::actingAs($this->client, 'client')
            ->test(PortalInvoiceShow::class, ['invoice' => $invoice])
            ->assertSee('Payment Summary')
            ->assertSee('TXN-99')
            ->assertSee(money(400, false))   // total paid
            ->assertSee(money(600, false));  // outstanding
    }

    public function test_portal_dashboard_shows_outstanding_balance(): void
    {
        $invoice = $this->makeInvoice('sent', 1000);
        $invoice->payments()->create(['amount' => 400, 'payment_method' => 'cash', 'payment_date' => now()]);

        Livewire::actingAs($this->client, 'client')
            ->test(PortalDashboard::class)
            ->assertSee('Outstanding')
            ->assertSee(money(1000))  // total invoiced
            ->assertSee(money(600));  // outstanding balance
    }

    public function test_client_cannot_view_another_clients_invoice(): void
    {
        $other = Client::create(['name' => 'Other', 'email' => 'other@example.com', 'portal_enabled' => true]);
        $invoice = $this->makeInvoice('sent', 1000);

        Livewire::actingAs($other, 'client')
            ->test(PortalInvoiceShow::class, ['invoice' => $invoice])
            ->assertStatus(403);
    }
}
