<?php

namespace Tests\Feature;

use App\Livewire\Admin\ConvertModal;
use App\Livewire\Admin\DuplicateModal;
use App\Livewire\Admin\Estimates\EstimatesIndex;
use App\Livewire\Admin\Invoices\InvoicesIndex;
use App\Models\Client;
use App\Models\Company;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\User;
use App\Services\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BulkConvertDuplicateTest extends TestCase
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

    protected function makeInvoice(string $status = 'draft', float $total = 1000): Invoice
    {
        $invoice = Invoice::create([
            'invoice_number' => DocumentNumberService::nextInvoiceNumber(),
            'client_id' => $this->client->id, 'status' => $status,
            'issue_date' => now(), 'subtotal' => $total, 'total' => $total,
        ]);
        $invoice->items()->create(['name' => 'Work', 'quantity' => 1, 'unit_price' => $total, 'total' => $total, 'order' => 0]);

        return $invoice;
    }

    protected function makeEstimate(string $status = 'sent'): Estimate
    {
        $estimate = Estimate::create([
            'estimate_number' => DocumentNumberService::nextEstimateNumber(),
            'client_id' => $this->client->id, 'status' => $status,
            'issue_date' => now(), 'subtotal' => 1500, 'total' => 1500,
        ]);
        $estimate->items()->create(['name' => 'Design', 'quantity' => 1, 'unit_price' => 1000, 'total' => 1000, 'order' => 0]);
        $estimate->items()->create(['name' => 'Hosting', 'quantity' => 1, 'unit_price' => 500, 'total' => 500, 'order' => 1]);

        return $estimate;
    }

    // --- Bulk actions ---

    public function test_select_all_selects_page_ids(): void
    {
        $a = $this->makeInvoice();
        $b = $this->makeInvoice();

        $component = Livewire::test(InvoicesIndex::class)->set('selectAll', true);
        $selected = $component->get('selected');
        sort($selected);

        $this->assertSame([(string) $a->id, (string) $b->id], $selected);
    }

    public function test_bulk_mark_sent_only_affects_drafts(): void
    {
        $draft = $this->makeInvoice('draft');
        $paid = $this->makeInvoice('paid');

        Livewire::test(InvoicesIndex::class)
            ->set('selected', [(string) $draft->id, (string) $paid->id])
            ->call('bulkMarkSent');

        $this->assertSame('sent', $draft->refresh()->status);
        $this->assertSame('paid', $paid->refresh()->status); // unchanged
    }

    public function test_bulk_mark_paid_updates_invoices(): void
    {
        $a = $this->makeInvoice('sent');
        $b = $this->makeInvoice('sent');

        Livewire::test(InvoicesIndex::class)
            ->set('selected', [(string) $a->id, (string) $b->id])
            ->call('bulkMarkPaid')
            ->assertSet('selected', []);

        $this->assertSame('paid', $a->refresh()->status);
        $this->assertSame('paid', $b->refresh()->status);
    }

    public function test_bulk_delete_soft_deletes_selected(): void
    {
        $a = $this->makeInvoice();
        $b = $this->makeInvoice();

        Livewire::test(InvoicesIndex::class)
            ->set('selected', [(string) $a->id])
            ->call('bulkDelete');

        $this->assertSoftDeleted($a);
        $this->assertNotSoftDeleted($b);
    }

    public function test_bulk_export_csv_downloads_file(): void
    {
        $a = $this->makeInvoice();

        Livewire::test(InvoicesIndex::class)
            ->set('selected', [(string) $a->id])
            ->call('bulkExportCsv')
            ->assertFileDownloaded('invoices-export-' . now()->format('Y-m-d') . '.csv');
    }

    public function test_estimate_bulk_mark_sent(): void
    {
        $estimate = $this->makeEstimate('draft');

        Livewire::test(EstimatesIndex::class)
            ->set('selected', [(string) $estimate->id])
            ->call('bulkMarkSent');

        $this->assertSame('sent', $estimate->refresh()->status);
    }

    // --- Duplicate ---

    public function test_duplicate_invoice_as_invoice(): void
    {
        $invoice = $this->makeInvoice('paid', 1000);

        Livewire::test(DuplicateModal::class)
            ->call('handleOpen', 'invoice', $invoice->id)
            ->assertSet('duplicateAs', 'invoice')
            ->call('duplicate')
            ->assertHasNoErrors();

        $copy = Invoice::where('id', '!=', $invoice->id)->first();
        $this->assertNotNull($copy);
        $this->assertSame('draft', $copy->status);
        $this->assertCount(1, $copy->items);
        $this->assertEquals(1000, (float) $copy->total);
    }

    public function test_duplicate_invoice_as_estimate(): void
    {
        $invoice = $this->makeInvoice('paid', 1000);

        Livewire::test(DuplicateModal::class)
            ->call('handleOpen', 'invoice', $invoice->id)
            ->set('duplicateAs', 'estimate')
            ->call('duplicate')
            ->assertHasNoErrors();

        $this->assertSame(1, Estimate::count());
        $estimate = Estimate::first();
        $this->assertSame('draft', $estimate->status);
        $this->assertCount(1, $estimate->items);
    }

    public function test_duplicate_requires_client(): void
    {
        $invoice = $this->makeInvoice();

        Livewire::test(DuplicateModal::class)
            ->call('handleOpen', 'invoice', $invoice->id)
            ->set('newClientId', null)
            ->call('duplicate')
            ->assertHasErrors('newClientId');
    }

    // --- Convert ---

    public function test_convert_estimate_to_invoice_full(): void
    {
        $estimate = $this->makeEstimate('sent');

        Livewire::test(ConvertModal::class)
            ->call('handleOpen', 'estimate_to_invoice', $estimate->id)
            ->call('convert')
            ->assertHasNoErrors();

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        $this->assertSame('draft', $invoice->status);
        $this->assertSame($estimate->estimate_number, $invoice->converted_from);
        $this->assertCount(2, $invoice->items);
        $this->assertEquals(1500, (float) $invoice->total);
        $this->assertSame('accepted', $estimate->refresh()->status); // markAccepted default
    }

    public function test_convert_estimate_to_invoice_partial(): void
    {
        $estimate = $this->makeEstimate('sent');
        $firstItemId = $estimate->items->first()->id; // total 1000

        Livewire::test(ConvertModal::class)
            ->call('handleOpen', 'estimate_to_invoice', $estimate->id)
            ->set('partialConvert', true)
            ->set('selectedItems', [(string) $firstItemId])
            ->call('convert')
            ->assertHasNoErrors();

        $invoice = Invoice::first();
        $this->assertCount(1, $invoice->items);
        $this->assertEquals(1000, (float) $invoice->total);
    }

    public function test_convert_requires_at_least_one_item(): void
    {
        $estimate = $this->makeEstimate('sent');

        Livewire::test(ConvertModal::class)
            ->call('handleOpen', 'estimate_to_invoice', $estimate->id)
            ->set('partialConvert', true)
            ->set('selectedItems', [])
            ->call('convert')
            ->assertHasErrors('selectedItems');

        $this->assertSame(0, Invoice::count());
    }

    public function test_convert_invoice_to_estimate(): void
    {
        $invoice = $this->makeInvoice('sent', 1000);

        Livewire::test(ConvertModal::class)
            ->call('handleOpen', 'invoice_to_estimate', $invoice->id)
            ->call('convert')
            ->assertHasNoErrors();

        $estimate = Estimate::first();
        $this->assertNotNull($estimate);
        $this->assertSame($invoice->invoice_number, $estimate->converted_from);
        $this->assertEquals(1000, (float) $estimate->total);
    }
}
