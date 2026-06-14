<?php

namespace Tests\Feature;

use App\Livewire\Admin\GlobalSearch;
use App\Models\Client;
use App\Models\Company;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\User;
use App\Services\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
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
            'name' => 'Globex Corp', 'email' => 'hi@globex.com', 'phone' => '0771234567', 'portal_enabled' => false,
        ]);
    }

    public function test_short_query_returns_no_results(): void
    {
        Livewire::test(GlobalSearch::class)
            ->set('query', 'a')
            ->assertSet('showResults', false)
            ->assertSet('results.clients', []);
    }

    public function test_finds_client_by_name(): void
    {
        Livewire::test(GlobalSearch::class)
            ->set('query', 'Globex')
            ->assertSet('showResults', true)
            ->assertCount('results.clients', 1);
    }

    public function test_finds_invoice_by_number(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => DocumentNumberService::nextInvoiceNumber(),
            'client_id' => $this->client->id, 'status' => 'sent',
            'issue_date' => now(), 'subtotal' => 500, 'total' => 500,
        ]);

        $component = Livewire::test(GlobalSearch::class)->set('query', $invoice->invoice_number);
        $this->assertCount(1, $component->get('results.invoices'));
        $this->assertSame($invoice->id, $component->get('results.invoices')[0]['id']);
    }

    public function test_finds_estimate_by_client_name(): void
    {
        Estimate::create([
            'estimate_number' => DocumentNumberService::nextEstimateNumber(),
            'client_id' => $this->client->id, 'status' => 'sent',
            'issue_date' => now(), 'subtotal' => 800, 'total' => 800,
        ]);

        $component = Livewire::test(GlobalSearch::class)->set('query', 'Globex');
        $this->assertCount(1, $component->get('results.estimates'));
    }

    public function test_clear_search_resets_state(): void
    {
        Livewire::test(GlobalSearch::class)
            ->set('query', 'Globex')
            ->assertSet('showResults', true)
            ->call('clearSearch')
            ->assertSet('query', '')
            ->assertSet('showResults', false)
            ->assertSet('results.clients', []);
    }
}
