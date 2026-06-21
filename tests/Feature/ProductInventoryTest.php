<?php

namespace Tests\Feature;

use App\Console\Commands\CheckLowStock;
use App\Livewire\Admin\Invoices\InvoiceForm;
use App\Livewire\Admin\Invoices\InvoiceShow;
use App\Livewire\Admin\Products\ProductForm;
use App\Livewire\Admin\Products\ProductShow;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProductInventoryTest extends TestCase
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
            'name' => 'Acme', 'email' => 'acme' . fake()->unique()->numerify('##') . '@x.com', 'password' => Hash::make('password123'),
        ]);
    }

    private function product(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'USB Drive 64GB', 'type' => 'product', 'unit' => 'unit',
            'sale_price' => 1200, 'purchase_cost' => 800, 'currency_code' => 'LKR',
            'track_inventory' => true, 'stock_quantity' => 20, 'low_stock_threshold' => 5, 'is_active' => true,
        ], $overrides));
    }

    public function test_profit_margin_and_stock_status_accessors(): void
    {
        $p = $this->product(['sale_price' => 1000, 'purchase_cost' => 700]);
        $this->assertEqualsWithDelta(30.0, $p->profit_margin, 0.01);
        $this->assertEqualsWithDelta(300, $p->profit_amount, 0.01);
        $this->assertSame('in_stock', $p->stock_status);

        $p->update(['stock_quantity' => 4]); // <= threshold 5
        $this->assertTrue($p->fresh()->is_low_stock);
        $this->assertSame('low_stock', $p->fresh()->stock_status);

        $p->update(['stock_quantity' => 0]);
        $this->assertSame('out_of_stock', $p->fresh()->stock_status);

        $service = $this->product(['type' => 'service', 'track_inventory' => false]);
        $this->assertSame('service', $service->stock_status);
        $this->assertNull($this->product(['purchase_cost' => null])->profit_margin);
    }

    public function test_adjust_stock_records_movement_and_updates_quantity(): void
    {
        $this->actingAs($this->admin());
        $p = $this->product(['stock_quantity' => 10]);

        $p->adjustStock(5, 'purchase', 'Restock');
        $this->assertEqualsWithDelta(15, (float) $p->fresh()->stock_quantity, 0.01);

        $p->adjustStock(-3, 'adjustment', 'Damaged');
        $p->refresh();
        $this->assertEqualsWithDelta(12, (float) $p->stock_quantity, 0.01);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $p->id, 'type' => 'purchase', 'quantity_after' => 15]);
        $this->assertSame(2, $p->movements()->count());
    }

    public function test_service_products_do_not_track_stock(): void
    {
        $p = $this->product(['type' => 'service', 'track_inventory' => false, 'stock_quantity' => 0]);
        $this->assertNull($p->adjustStock(5, 'purchase'));
        $this->assertSame(0, $p->movements()->count());
    }

    public function test_product_form_creates_product_with_opening_stock(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(ProductForm::class)
            ->set('name', 'Keyboard')
            ->set('sale_price', 4500)
            ->set('purchase_cost', 3000)
            ->set('type', 'product')
            ->set('track_inventory', true)
            ->set('stock_quantity', 12)
            ->set('low_stock_threshold', 4)
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::where('name', 'Keyboard')->first();
        $this->assertNotNull($product);
        $this->assertEqualsWithDelta(12, (float) $product->stock_quantity, 0.01);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'opening']);
    }

    public function test_adding_product_to_invoice_sets_line_item_and_product_id(): void
    {
        $this->actingAs($this->admin());
        $client = $this->client();
        $product = $this->product();

        Livewire::test(InvoiceForm::class)
            ->set('client_id', $client->id)
            ->call('addProduct', $product->id)
            ->assertSet('items.0.product_id', $product->id)
            ->assertSet('items.0.name', 'USB Drive 64GB')
            ->assertSet('items.0.unit_price', 1200.0);
    }

    public function test_marking_invoice_sent_deducts_stock_once(): void
    {
        $this->actingAs($this->admin());
        $client = $this->client();
        $product = $this->product(['stock_quantity' => 20]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-STK1', 'client_id' => $client->id, 'status' => 'draft',
            'currency_code' => 'LKR', 'exchange_rate' => 1, 'issue_date' => today(), 'tax_rate' => 0, 'discount_amount' => 0,
        ]);
        $invoice->items()->create(['product_id' => $product->id, 'name' => $product->name, 'quantity' => 3, 'unit_price' => 1200, 'total' => 3600, 'order' => 0]);

        Livewire::test(InvoiceShow::class, ['invoice' => $invoice])->call('setStatus', 'sent');

        $this->assertEqualsWithDelta(17, (float) $product->fresh()->stock_quantity, 0.01);
        $this->assertTrue($invoice->fresh()->stock_deducted);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'sale', 'reference_label' => 'INV-STK1']);

        // Marking paid afterwards must NOT deduct again.
        Livewire::test(InvoiceShow::class, ['invoice' => $invoice->fresh()])->call('setStatus', 'paid');
        $this->assertEqualsWithDelta(17, (float) $product->fresh()->stock_quantity, 0.01);
        $this->assertSame(1, $product->movements()->where('type', 'sale')->count());
    }

    public function test_adjust_stock_via_show_component(): void
    {
        $this->actingAs($this->admin());
        $product = $this->product(['stock_quantity' => 10]);

        Livewire::test(ProductShow::class, ['product' => $product])
            ->set('adjustType', 'purchase')
            ->set('adjustQuantity', 25)
            ->set('adjustNotes', 'New shipment')
            ->call('adjustStock')
            ->assertHasNoErrors();

        $this->assertEqualsWithDelta(35, (float) $product->fresh()->stock_quantity, 0.01);
    }

    public function test_low_stock_command_logs_alerts(): void
    {
        $this->product(['stock_quantity' => 3, 'low_stock_threshold' => 5]); // low
        $this->product(['name' => 'Plenty', 'stock_quantity' => 50, 'low_stock_threshold' => 5]); // ok

        $this->artisan('inventory:check-low-stock')->assertExitCode(CheckLowStock::SUCCESS);

        $this->assertSame(1, \App\Models\ActivityLog::where('type', 'low_stock_alert')->count());
    }

    public function test_products_index_loads(): void
    {
        $this->actingAs($this->admin());
        $this->get(route('admin.products.index'))->assertOk()->assertSee('Products & Inventory');
    }
}
