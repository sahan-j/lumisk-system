<?php

namespace Tests\Feature;

use App\Livewire\Admin\Subscriptions\SubscriptionForm;
use App\Livewire\Admin\Subscriptions\SubscriptionShow;
use App\Models\Client;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
        $this->seed(PermissionsSeeder::class);
    }

    private function client(): Client
    {
        return Client::create([
            'name' => 'Acme', 'email' => 'acme@x.com', 'password' => Hash::make('password123'),
        ]);
    }

    private function admin(string $role = 'super_admin'): User
    {
        return User::create([
            'name' => ucfirst($role), 'email' => $role . fake()->unique()->numerify('##') . '@x.com',
            'password' => Hash::make('password123'), 'role' => $role, 'is_active' => true,
        ]);
    }

    private function makeSubscription(array $overrides = []): Subscription
    {
        return Subscription::create(array_merge([
            'subscription_number' => Subscription::generateNumber(),
            'client_id' => $this->client()->id,
            'name' => 'Hosting',
            'amount' => 8000,
            'currency' => 'LKR',
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'start_date' => today()->subMonth(),
            'next_billing_date' => today(),
            'auto_invoice' => true,
            'auto_send_invoice' => false,
        ], $overrides));
    }

    public function test_subscription_number_generates_sequentially(): void
    {
        $first = Subscription::generateNumber();
        $second = Subscription::generateNumber();

        $this->assertSame('SUB-001', $first);
        $this->assertSame('SUB-002', $second);
    }

    public function test_yearly_and_monthly_value_accessors(): void
    {
        $sub = $this->makeSubscription(['billing_cycle' => 'annual', 'amount' => 12000]);

        $this->assertEqualsWithDelta(12000, $sub->yearly_value, 0.01);
        $this->assertEqualsWithDelta(1000, $sub->monthly_value, 0.01);
    }

    public function test_generate_invoice_links_invoice_and_rolls_billing_date(): void
    {
        $sub = $this->makeSubscription(['next_billing_date' => today()]);

        $invoice = $sub->generateInvoice();
        $sub->refresh();

        $this->assertEqualsWithDelta(8000, (float) $invoice->total, 0.01);
        $this->assertCount(1, $invoice->items);
        $this->assertDatabaseHas('subscription_invoices', [
            'subscription_id' => $sub->id, 'invoice_id' => $invoice->id,
        ]);
        $this->assertSame(today()->addMonth()->toDateString(), $sub->next_billing_date->toDateString());
        $this->assertSame(today()->toDateString(), $sub->last_billed_date->toDateString());
    }

    public function test_process_billing_command_bills_due_subscriptions(): void
    {
        Mail::fake();
        $sub = $this->makeSubscription(['next_billing_date' => today()]);

        $this->artisan('subscriptions:process-billing')->assertSuccessful();

        $sub->refresh();
        $this->assertSame(1, $sub->invoices()->count());
        $this->assertTrue($sub->next_billing_date->isFuture());
    }

    public function test_process_billing_skips_paused_subscriptions(): void
    {
        $sub = $this->makeSubscription(['status' => 'paused', 'next_billing_date' => today()]);

        $this->artisan('subscriptions:process-billing')->assertSuccessful();

        $this->assertSame(0, $sub->invoices()->count());
    }

    public function test_trial_subscription_activates_when_trial_ends(): void
    {
        $sub = $this->makeSubscription(['status' => 'trial', 'trial_end_date' => today()->subDay()]);

        $this->artisan('subscriptions:process-billing')->assertSuccessful();

        $sub->refresh();
        $this->assertSame('active', $sub->status);
    }

    public function test_form_creates_subscription_with_trial(): void
    {
        $admin = $this->admin();
        $client = $this->client();

        Livewire::actingAs($admin)
            ->test(SubscriptionForm::class)
            ->set('client_id', $client->id)
            ->set('name', 'Retainer')
            ->set('amount', 20000)
            ->set('billing_cycle', 'monthly')
            ->set('start_date', today()->toDateString())
            ->set('trial_days', 14)
            ->call('save')
            ->assertHasNoErrors();

        $sub = Subscription::where('name', 'Retainer')->first();
        $this->assertNotNull($sub);
        $this->assertSame('trial', $sub->status);
        $this->assertSame(today()->addDays(14)->toDateString(), $sub->trial_end_date->toDateString());
    }

    public function test_show_actions_pause_resume_cancel(): void
    {
        $admin = $this->admin();
        $sub = $this->makeSubscription();

        Livewire::actingAs($admin)->test(SubscriptionShow::class, ['subscription' => $sub])
            ->call('pause');
        $this->assertSame('paused', $sub->fresh()->status);

        Livewire::actingAs($admin)->test(SubscriptionShow::class, ['subscription' => $sub->fresh()])
            ->call('resume');
        $this->assertSame('active', $sub->fresh()->status);

        Livewire::actingAs($admin)->test(SubscriptionShow::class, ['subscription' => $sub->fresh()])
            ->set('cancellationReason', 'No longer needed')
            ->call('cancel');
        $this->assertSame('cancelled', $sub->fresh()->status);
        $this->assertNotNull($sub->fresh()->cancelled_at);
    }

    public function test_staff_can_view_but_not_create_subscriptions(): void
    {
        $staff = $this->admin('staff');

        $this->actingAs($staff)->get('/admin/subscriptions')->assertOk();
        $this->actingAs($staff)->get('/admin/subscriptions/create')->assertForbidden();
    }
}
