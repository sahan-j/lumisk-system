<?php

namespace Tests\Feature;

use App\Livewire\Admin\Reports\ClientReport;
use App\Livewire\Admin\Reports\InvoiceAgingReport;
use App\Livewire\Admin\Reports\ProfitLossReport;
use App\Livewire\Admin\Reports\RevenueReport;
use App\Livewire\Admin\Reports\TaxReport;
use App\Models\Client;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
        $this->admin = User::factory()->create();
        $this->client = Client::create(['name' => 'Acme Ltd', 'email' => 'hi@acme.com']);
    }

    private function invoice(array $attrs = []): Invoice
    {
        return Invoice::create(array_merge([
            'invoice_number' => 'INV-' . fake()->unique()->numerify('###'),
            'client_id' => $this->client->id,
            'status' => 'paid',
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'subtotal' => 10000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 10000,
        ], $attrs));
    }

    public function test_revenue_report_sums_payments_in_range(): void
    {
        $invoice = $this->invoice();
        $invoice->payments()->create(['amount' => 7500, 'payment_method' => 'cash', 'payment_date' => now()]);
        // Out-of-range payment should be excluded from "this month".
        $old = $this->invoice();
        $old->payments()->create(['amount' => 9999, 'payment_method' => 'cash', 'payment_date' => now()->subMonths(3)]);

        Livewire::actingAs($this->admin)
            ->test(RevenueReport::class)
            ->set('period', 'this_month')
            ->assertSee('Acme Ltd')
            ->assertSee(money(7500));
    }

    public function test_profit_loss_computes_net_and_margin(): void
    {
        $invoice = $this->invoice();
        $invoice->payments()->create(['amount' => 10000, 'payment_method' => 'cash', 'payment_date' => now()]);
        Expense::create(['title' => 'Hosting', 'amount' => 4000, 'expense_date' => now(), 'payment_method' => 'card']);

        Livewire::actingAs($this->admin)
            ->test(ProfitLossReport::class)
            ->set('period', 'this_month')
            ->assertSee(money(10000))   // revenue
            ->assertSee(money(4000))    // expenses
            ->assertSee(money(6000))    // net profit
            ->assertSee('60% margin');
    }

    public function test_tax_report_groups_by_rate(): void
    {
        $this->invoice([
            'status' => 'sent', 'subtotal' => 10000, 'tax_rate' => 15, 'tax_amount' => 1500, 'total' => 11500,
        ]);

        Livewire::actingAs($this->admin)
            ->test(TaxReport::class)
            ->set('period', 'this_month')
            ->assertSee(money(1500))
            ->assertSee('15%');
    }

    public function test_invoice_aging_buckets_overdue_invoices(): void
    {
        // 45 days overdue → 31-60 bucket.
        $this->invoice(['status' => 'overdue', 'due_date' => today()->subDays(45)]);

        Livewire::actingAs($this->admin)
            ->test(InvoiceAgingReport::class)
            ->assertSee('31–60 days')
            ->assertSee('Acme Ltd');
    }

    public function test_client_report_export_downloads(): void
    {
        $invoice = $this->invoice();
        $invoice->payments()->create(['amount' => 5000, 'payment_method' => 'cash', 'payment_date' => now()]);

        Livewire::actingAs($this->admin)
            ->test(ClientReport::class)
            ->call('export')
            ->assertFileDownloaded();
    }

    public function test_custom_date_range_resolves(): void
    {
        $invoice = $this->invoice(['issue_date' => '2026-01-15']);
        $invoice->payments()->create(['amount' => 3210, 'payment_method' => 'cash', 'payment_date' => '2026-01-20']);

        Livewire::actingAs($this->admin)
            ->test(RevenueReport::class)
            ->set('period', 'custom')
            ->set('dateFrom', '2026-01-01')
            ->set('dateTo', '2026-01-31')
            ->assertSee(money(3210));
    }
}
