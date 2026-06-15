<?php

namespace Tests\Feature;

use App\Livewire\Admin\Clients\ClientsIndex;
use App\Livewire\Admin\Dashboard;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityFeedTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
        $this->admin = User::factory()->create(['name' => 'Sahan']);
    }

    public function test_creating_a_client_logs_activity_with_admin_causer(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ClientsIndex::class)
            ->call('create')
            ->set('name', 'Acme Ltd')
            ->set('email', 'hi@acme.com')
            ->call('save');

        $log = ActivityLog::where('type', 'client_created')->first();
        $this->assertNotNull($log);
        $this->assertSame('admin', $log->causer_type);
        $this->assertSame('Sahan', $log->causer_name);
        $this->assertStringContainsString('Acme Ltd', $log->description);
    }

    public function test_log_helper_resolves_color_and_icon(): void
    {
        $log = ActivityLog::log('invoice_paid', 'Invoice LT-007 fully paid', [
            'subject_label' => 'LT-007',
        ]);

        $this->assertSame('#10b981', $log->color);
        $this->assertNotEmpty($log->icon_path);
        $this->assertSame('LT-007', $log->subject_label);
    }

    public function test_dashboard_filter_limits_to_type_group(): void
    {
        ActivityLog::log('invoice_created', 'Invoice A created', ['subject_label' => 'A']);
        ActivityLog::log('payment_recorded', 'Payment for A', ['subject_label' => 'A']);
        ActivityLog::log('ticket_created', 'Ticket opened', ['subject_label' => 'T-1']);

        Livewire::actingAs($this->admin)
            ->test(Dashboard::class)
            ->assertSee('Invoice A created')
            ->assertSee('Payment for A')
            ->call('filterActivity', 'payments')
            ->assertSee('Payment for A')
            ->assertDontSee('Invoice A created')
            ->assertDontSee('Ticket opened');
    }

    public function test_load_more_grows_the_limit(): void
    {
        // Distinct, descending timestamps so ordering is deterministic.
        foreach (range(1, 20) as $i) {
            $log = ActivityLog::log('invoice_created', "ActivityItem-{$i}", ['subject_label' => "N{$i}"]);
            $log->created_at = now()->subMinutes($i); // item 1 newest … item 20 oldest
            $log->save();
        }

        $component = Livewire::actingAs($this->admin)->test(Dashboard::class);
        // 15 newest shown → item 15 visible, item 16 not yet.
        $component->assertSee('ActivityItem-15')->assertDontSee('ActivityItem-16');
        $component->call('loadMore')
            ->assertSet('activityLimit', 30)
            ->assertSee('ActivityItem-16');
    }

    public function test_cleanup_command_deletes_old_logs(): void
    {
        $old = ActivityLog::log('invoice_created', 'Old one', []);
        $old->created_at = now()->subDays(120); // created_at is not mass-assignable — set directly
        $old->save();
        ActivityLog::log('invoice_created', 'Recent one', []);

        $this->artisan('activity:clean')->assertSuccessful();

        $this->assertDatabaseMissing('activity_logs', ['description' => 'Old one']);
        $this->assertDatabaseHas('activity_logs', ['description' => 'Recent one']);
    }

    public function test_portal_activity_is_scoped_to_the_client(): void
    {
        $client = Client::create(['name' => 'Mine', 'email' => 'm@x.com', 'portal_enabled' => true, 'password' => bcrypt('secret123')]);
        $other = Client::create(['name' => 'Other', 'email' => 'o@x.com']);

        ActivityLog::log('invoice_created', 'Yours', ['client_id' => $client->id, 'subject_label' => 'Y-1']);
        ActivityLog::log('invoice_created', 'Theirs', ['client_id' => $other->id, 'subject_label' => 'T-1']);

        Livewire::actingAs($client, 'client')
            ->test(\App\Livewire\Portal\Dashboard::class)
            ->assertSee('Yours')
            ->assertDontSee('Theirs');
    }
}
