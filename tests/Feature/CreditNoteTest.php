<?php

namespace Tests\Feature;

use App\Livewire\Admin\CreditNotes\CreditNoteForm;
use App\Livewire\Admin\CreditNotes\CreditNoteShow;
use App\Mail\CreditNoteMail;
use App\Models\Client;
use App\Models\Company;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class CreditNoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
        $this->seed(PermissionsSeeder::class);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin' . fake()->unique()->numerify('##') . '@x.com',
            'password' => Hash::make('password123'), 'role' => 'super_admin', 'is_active' => true,
        ]);
    }

    private function client(): Client
    {
        return Client::create([
            'name' => 'Acme', 'email' => 'acme' . fake()->unique()->numerify('##') . '@x.com',
            'password' => Hash::make('password123'),
        ]);
    }

    private function invoice(Client $client, float $total = 10000, string $status = 'sent'): Invoice
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-' . fake()->unique()->numerify('###'),
            'client_id' => $client->id,
            'status' => $status,
            'issue_date' => today(),
            'tax_rate' => 0,
            'discount_amount' => 0,
        ]);
        $invoice->items()->create(['name' => 'Work', 'quantity' => 1, 'unit_price' => $total, 'total' => $total, 'order' => 0]);
        $invoice->load('items');
        $invoice->recalculateTotals();
        $invoice->save();

        return $invoice;
    }

    private function creditNote(Client $client, float $unit = 5000, string $status = 'issued'): CreditNote
    {
        $cn = CreditNote::create([
            'credit_note_number' => CreditNote::generateNumber(),
            'client_id' => $client->id,
            'status' => $status,
            'issue_date' => today(),
            'reason' => 'Partial refund',
            'tax_rate' => 0,
        ]);
        $cn->items()->create(['name' => 'Refund', 'quantity' => 1, 'unit_price' => $unit, 'total' => $unit, 'sort_order' => 0]);
        $cn->load('items');
        $cn->recalculateTotals();
        $cn->save();

        return $cn;
    }

    public function test_generate_number_increments_company_counter(): void
    {
        $a = CreditNote::generateNumber();
        $b = CreditNote::generateNumber();

        $this->assertSame('CN-001', $a);
        $this->assertSame('CN-002', $b);
    }

    public function test_recalculate_totals_includes_tax(): void
    {
        $cn = $this->creditNote($this->client(), 10000, 'draft');
        $cn->update(['tax_rate' => 10]);
        $cn->load('items');
        $cn->recalculateTotals();

        $this->assertEqualsWithDelta(10000, (float) $cn->subtotal, 0.001);
        $this->assertEqualsWithDelta(1000, (float) $cn->tax_amount, 0.001);
        $this->assertEqualsWithDelta(11000, (float) $cn->total, 0.001);
    }

    public function test_creating_a_credit_note_via_form_generates_number_and_logs_activity(): void
    {
        $this->actingAs($this->admin());
        $client = $this->client();

        Livewire::test(CreditNoteForm::class)
            ->set('client_id', $client->id)
            ->set('reason', 'Pricing error')
            ->set('items', [['name' => 'Overcharge refund', 'description' => null, 'quantity' => 1, 'unit_price' => 7500]])
            ->call('save')
            ->assertHasNoErrors();

        $cn = CreditNote::first();
        $this->assertSame('CN-001', $cn->credit_note_number);
        $this->assertSame('draft', $cn->status);
        $this->assertEqualsWithDelta(7500, (float) $cn->total, 0.001);
        $this->assertDatabaseHas('activity_logs', ['type' => 'credit_note_created']);
    }

    public function test_amount_remaining_and_fully_applied_accessors(): void
    {
        $cn = $this->creditNote($this->client(), 5000);
        $this->assertEqualsWithDelta(5000, $cn->amount_remaining, 0.001);
        $this->assertFalse($cn->is_fully_applied);

        $cn->update(['amount_applied' => 5000, 'status' => 'applied']);
        $cn->refresh();
        $this->assertEqualsWithDelta(0, $cn->amount_remaining, 0.001);
        $this->assertTrue($cn->is_fully_applied);
    }

    public function test_only_draft_credit_notes_can_be_issued(): void
    {
        $this->actingAs($this->admin());
        $cn = $this->creditNote($this->client(), 5000, 'draft');

        Livewire::test(CreditNoteShow::class, ['creditNote' => $cn])->call('issue');
        $this->assertSame('issued', $cn->fresh()->status);

        // Issuing again is a no-op (stays issued).
        Livewire::test(CreditNoteShow::class, ['creditNote' => $cn->fresh()])->call('issue');
        $this->assertSame('issued', $cn->fresh()->status);
    }

    public function test_applying_a_credit_note_posts_a_payment_and_marks_invoice_paid(): void
    {
        $this->actingAs($this->admin());
        $client = $this->client();
        $invoice = $this->invoice($client, 5000, 'sent');
        $cn = $this->creditNote($client, 5000, 'issued');

        Livewire::test(CreditNoteShow::class, ['creditNote' => $cn])
            ->set('applyInvoiceId', $invoice->id)
            ->set('applyAmount', 5000)
            ->call('apply')
            ->assertHasNoErrors();

        $cn->refresh();
        $this->assertEqualsWithDelta(5000, (float) $cn->amount_applied, 0.001);
        $this->assertSame('applied', $cn->status);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'reference_number' => $cn->credit_note_number,
        ]);
        $this->assertDatabaseHas('credit_note_applications', [
            'credit_note_id' => $cn->id,
            'invoice_id' => $invoice->id,
        ]);
        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_partial_application_keeps_status_issued(): void
    {
        $this->actingAs($this->admin());
        $client = $this->client();
        $invoice = $this->invoice($client, 10000, 'sent');
        $cn = $this->creditNote($client, 5000, 'issued');

        Livewire::test(CreditNoteShow::class, ['creditNote' => $cn])
            ->set('applyInvoiceId', $invoice->id)
            ->set('applyAmount', 3000)
            ->call('apply')
            ->assertHasNoErrors();

        $cn->refresh();
        $this->assertEqualsWithDelta(3000, (float) $cn->amount_applied, 0.001);
        $this->assertEqualsWithDelta(2000, $cn->amount_remaining, 0.001);
        $this->assertSame('issued', $cn->status);
        $this->assertSame('sent', $invoice->fresh()->status);
    }

    public function test_cannot_apply_more_than_remaining_balance(): void
    {
        $this->actingAs($this->admin());
        $client = $this->client();
        $invoice = $this->invoice($client, 10000, 'sent');
        $cn = $this->creditNote($client, 5000, 'issued');

        Livewire::test(CreditNoteShow::class, ['creditNote' => $cn])
            ->set('applyInvoiceId', $invoice->id)
            ->set('applyAmount', 9000)
            ->call('apply')
            ->assertHasErrors('applyAmount');

        $this->assertEqualsWithDelta(0, (float) $cn->fresh()->amount_applied, 0.001);
    }

    public function test_cannot_void_an_applied_credit_note(): void
    {
        $this->actingAs($this->admin());
        $cn = $this->creditNote($this->client(), 5000, 'issued');
        $cn->update(['amount_applied' => 2000]);

        Livewire::test(CreditNoteShow::class, ['creditNote' => $cn->fresh()])->call('void');
        $this->assertNotSame('void', $cn->fresh()->status);
    }

    public function test_void_works_when_nothing_applied(): void
    {
        $this->actingAs($this->admin());
        $cn = $this->creditNote($this->client(), 5000, 'issued');

        Livewire::test(CreditNoteShow::class, ['creditNote' => $cn])->call('void');
        $this->assertSame('void', $cn->fresh()->status);
    }

    public function test_send_to_client_emails_the_credit_note(): void
    {
        Mail::fake();
        $this->actingAs($this->admin());
        $cn = $this->creditNote($this->client(), 5000, 'issued');

        Livewire::test(CreditNoteShow::class, ['creditNote' => $cn])->call('sendToClient');

        Mail::assertSent(CreditNoteMail::class);
    }

    public function test_credit_notes_index_loads(): void
    {
        $this->actingAs($this->admin());
        $this->get(route('admin.credit-notes.index'))->assertOk()->assertSee('Credit Notes');
    }

    public function test_pdf_renders(): void
    {
        $this->actingAs($this->admin());
        $cn = $this->creditNote($this->client(), 5000, 'issued');

        $response = $this->get(route('admin.credit-notes.pdf', $cn));
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }
}
