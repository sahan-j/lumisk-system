<?php

namespace Tests\Feature;

use App\Livewire\Admin\Clients\ClientShow;
use App\Livewire\Admin\Expenses\ExpenseForm;
use App\Livewire\Admin\Expenses\ExpensesIndex;
use App\Livewire\Admin\Settings\ExpenseCategoriesManager;
use App\Models\Client;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ExpenseModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected ExpenseCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
        $this->admin = User::factory()->create();
        $this->category = ExpenseCategory::create(['name' => 'Software', 'color' => '#00d4ff', 'icon' => 'tag']);
    }

    public function test_admin_creates_expense_with_receipt(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->admin)
            ->test(ExpenseForm::class)
            ->set('title', 'Adobe subscription')
            ->set('amount', 5400)
            ->set('expense_date', now()->format('Y-m-d'))
            ->set('category_id', $this->category->id)
            ->set('payment_method', 'card')
            ->set('receipt', UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'))
            ->call('save')
            ->assertRedirect(route('admin.expenses.index'));

        $expense = Expense::first();
        $this->assertNotNull($expense);
        $this->assertSame('Adobe subscription', $expense->title);
        $this->assertSame('5400.00', $expense->amount);
        $this->assertNotNull($expense->receipt);
        Storage::disk('public')->assertExists($expense->receipt);
    }

    public function test_expenses_index_csv_export_downloads(): void
    {
        Expense::create([
            'title' => 'Domain renewal', 'amount' => 1200, 'expense_date' => now(),
            'category_id' => $this->category->id, 'payment_method' => 'card',
        ]);

        Livewire::actingAs($this->admin)
            ->test(ExpensesIndex::class)
            ->call('exportCsv')
            ->assertFileDownloaded();
    }

    public function test_create_invoice_from_billable_expenses(): void
    {
        $client = Client::create(['name' => 'Acme', 'email' => 'a@acme.com']);

        $billable = Expense::create([
            'title' => 'Server hosting', 'amount' => 8000, 'expense_date' => now(),
            'client_id' => $client->id, 'payment_method' => 'bank_transfer',
            'is_billable' => true, 'is_billed' => false,
        ]);
        // Non-billable expense for same client should be ignored.
        Expense::create([
            'title' => 'Coffee', 'amount' => 500, 'expense_date' => now(),
            'client_id' => $client->id, 'payment_method' => 'cash', 'is_billable' => false,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ClientShow::class, ['client' => $client])
            ->call('createInvoiceFromExpenses');

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        $this->assertSame($client->id, $invoice->client_id);
        $this->assertSame(1, $invoice->items()->count());
        $this->assertEqualsWithDelta(8000, (float) $invoice->total, 0.01);
        $this->assertTrue($billable->fresh()->is_billed);
    }

    public function test_category_with_expenses_cannot_be_deleted(): void
    {
        Expense::create([
            'title' => 'Tool', 'amount' => 100, 'expense_date' => now(),
            'category_id' => $this->category->id, 'payment_method' => 'cash',
        ]);

        Livewire::actingAs($this->admin)
            ->test(ExpenseCategoriesManager::class)
            ->call('delete', $this->category->id);

        $this->assertDatabaseHas('expense_categories', ['id' => $this->category->id]);
    }
}
