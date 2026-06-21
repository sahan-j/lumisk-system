<?php

namespace Tests\Feature;

use App\Helpers\CurrencyHelper;
use App\Livewire\Admin\Invoices\InvoiceForm;
use App\Livewire\Admin\Settings\CurrencyManager;
use App\Models\Client;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class MultiCurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
        $this->seed(PermissionsSeeder::class);
        $this->seed(CurrencySeeder::class);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin' . fake()->unique()->numerify('##') . '@x.com',
            'password' => Hash::make('password123'), 'role' => 'super_admin', 'is_active' => true,
        ]);
    }

    private function client(array $overrides = []): Client
    {
        return Client::create(array_merge([
            'name' => 'Acme', 'email' => 'acme' . fake()->unique()->numerify('##') . '@x.com',
            'password' => Hash::make('password123'),
        ], $overrides));
    }

    public function test_seeder_creates_currencies_with_lkr_default(): void
    {
        $this->assertSame(8, Currency::count());
        $this->assertSame('LKR', Currency::getDefault()->code);
        $this->assertSame('$', Currency::getByCode('USD')->symbol);
    }

    public function test_active_scope_excludes_inactive(): void
    {
        Currency::getByCode('SGD')->update(['is_active' => false]);
        $codes = CurrencyHelper::getActiveCurrencies()->pluck('code');
        $this->assertTrue($codes->contains('USD'));
        $this->assertFalse($codes->contains('SGD'));
    }

    public function test_helper_format_and_tolkr(): void
    {
        $this->assertSame('$ 1,000.00', CurrencyHelper::format(1000, 'USD'));
        $this->assertSame('Rs 500.00', CurrencyHelper::format(500, 'LKR'));
        $this->assertEqualsWithDelta(305000, CurrencyHelper::toLkr(1000, 'USD'), 0.01);
        $this->assertEqualsWithDelta(1000, CurrencyHelper::toLkr(1000, 'LKR'), 0.01);
        $this->assertEqualsWithDelta(3100, CurrencyHelper::toLkr(10, 'USD', 310), 0.01);
    }

    public function test_invoice_currency_symbol_and_format(): void
    {
        $invoice = new Invoice(['currency_code' => 'EUR']);
        $this->assertSame('€', $invoice->currency_symbol);
        $this->assertSame('€ 1,250.00', $invoice->formatAmount(1250));
        $this->assertTrue($invoice->is_foreign_currency);

        $lkr = new Invoice(['currency_code' => 'LKR']);
        $this->assertFalse($lkr->is_foreign_currency);
    }

    public function test_recalculate_totals_sets_lkr_equivalent(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-CUR1', 'client_id' => $this->client()->id, 'status' => 'draft',
            'currency_code' => 'USD', 'exchange_rate' => 300, 'issue_date' => today(), 'tax_rate' => 0,
            'discount_amount' => 0,
        ]);
        $invoice->items()->create(['name' => 'Job', 'quantity' => 1, 'unit_price' => 100, 'total' => 100, 'order' => 0]);
        $invoice->load('items');
        $invoice->recalculateTotals();
        $invoice->save();

        $this->assertEqualsWithDelta(100, (float) $invoice->total, 0.01);
        $this->assertEqualsWithDelta(30000, (float) $invoice->total_lkr, 0.01);
    }

    public function test_invoice_form_stores_currency_and_lkr_total(): void
    {
        $this->actingAs($this->admin());
        $client = $this->client();

        Livewire::test(InvoiceForm::class)
            ->set('client_id', $client->id)
            ->set('currencyCode', 'USD') // fires updatedCurrencyCode automatically
            ->assertSet('exchangeRate', 305.0)
            ->assertSet('currencySymbol', '$')
            ->set('items', [['name' => 'Design', 'description' => null, 'quantity' => 2, 'unit_price' => 50]])
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::latest('id')->first();
        $this->assertSame('USD', $invoice->currency_code);
        $this->assertEqualsWithDelta(305, (float) $invoice->exchange_rate, 0.01);
        $this->assertEqualsWithDelta(100, (float) $invoice->total, 0.01);
        $this->assertEqualsWithDelta(30500, (float) $invoice->total_lkr, 0.01);
    }

    public function test_invoice_form_applies_client_default_currency(): void
    {
        $this->actingAs($this->admin());
        $client = $this->client(['default_currency' => 'GBP']);

        Livewire::test(InvoiceForm::class)
            ->set('client_id', $client->id) // fires updatedClientId automatically
            ->assertSet('currencyCode', 'GBP')
            ->assertSet('exchangeRate', 390.0);
    }

    public function test_lkr_invoice_keeps_rate_one(): void
    {
        $this->actingAs($this->admin());
        $client = $this->client();

        Livewire::test(InvoiceForm::class)
            ->set('client_id', $client->id)
            ->set('items', [['name' => 'Work', 'description' => null, 'quantity' => 1, 'unit_price' => 5000]])
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::latest('id')->first();
        $this->assertSame('LKR', $invoice->currency_code);
        $this->assertEqualsWithDelta(1, (float) $invoice->exchange_rate, 0.0001);
        $this->assertEqualsWithDelta(5000, (float) $invoice->total_lkr, 0.01);
    }

    public function test_currency_manager_updates_rate_and_protects_lkr(): void
    {
        $this->actingAs($this->admin());
        $usd = Currency::getByCode('USD');

        Livewire::test(CurrencyManager::class)
            ->set("rates.{$usd->id}", 312.5)
            ->call('updateRate', $usd->id);

        $usd->refresh();
        $this->assertEqualsWithDelta(312.5, (float) $usd->exchange_rate, 0.01);
        $this->assertNotNull($usd->updated_at_rate);

        // LKR rate can't be changed.
        $lkr = Currency::getByCode('LKR');
        Livewire::test(CurrencyManager::class)
            ->set("rates.{$lkr->id}", 99)
            ->call('updateRate', $lkr->id);
        $this->assertEqualsWithDelta(1, (float) $lkr->fresh()->exchange_rate, 0.0001);
    }

    public function test_currency_manager_toggles_active_but_not_lkr(): void
    {
        $this->actingAs($this->admin());
        $sgd = Currency::getByCode('SGD');

        Livewire::test(CurrencyManager::class)->call('toggle', $sgd->id);
        $this->assertFalse($sgd->fresh()->is_active);

        $lkr = Currency::getByCode('LKR');
        Livewire::test(CurrencyManager::class)->call('toggle', $lkr->id);
        $this->assertTrue($lkr->fresh()->is_active);
    }

    public function test_usd_invoice_pdf_renders(): void
    {
        $this->actingAs($this->admin());
        $invoice = Invoice::create([
            'invoice_number' => 'INV-USD1', 'client_id' => $this->client()->id, 'status' => 'sent',
            'currency_code' => 'USD', 'exchange_rate' => 305, 'issue_date' => today(), 'tax_rate' => 0,
            'discount_amount' => 0,
        ]);
        $invoice->items()->create(['name' => 'Consulting', 'quantity' => 1, 'unit_price' => 1000, 'total' => 1000, 'order' => 0]);
        $invoice->load('items');
        $invoice->recalculateTotals();
        $invoice->save();

        $response = $this->get(route('admin.invoices.pdf', $invoice));
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }
}
