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

    public function test_dashboard_activity_widget_shows_recent_activity(): void
    {
        ActivityLog::log('invoice_created', 'Invoice A created', ['subject_label' => 'A']);
        ActivityLog::log('payment_recorded', 'Payment for A', ['subject_label' => 'A']);
        ActivityLog::log('ticket_created', 'Ticket opened', ['subject_label' => 'T-1']);

        // The activity_feed widget is part of the default dashboard layout.
        Livewire::actingAs($this->admin)
            ->test(Dashboard::class)
            ->assertSee('Invoice A created')
            ->assertSee('Payment for A')
            ->assertSee('Ticket opened');
    }

    public function test_activity_widget_caps_at_latest_ten(): void
    {
        // Distinct, descending timestamps so ordering is deterministic.
        foreach (range(1, 12) as $i) {
            $log = ActivityLog::log('invoice_created', "ActivityItem-{$i}", ['subject_label' => "N{$i}"]);
            $log->created_at = now()->subMinutes($i); // item 1 newest … item 12 oldest
            $log->save();
        }

        // Widget shows the 10 newest → items 1..10 visible, 11 & 12 not.
        Livewire::actingAs($this->admin)
            ->test(Dashboard::class)
            ->assertSee('ActivityItem-1')
            ->assertSee('ActivityItem-10')
            ->assertDontSee('ActivityItem-11');
    }

    public function test_dashboard_layout_persists_and_resets(): void
    {
        $component = Livewire::actingAs($this->admin)->test(Dashboard::class);

        // Hiding a widget persists to dashboard_preferences.
        $component->call('toggleWidget', 'mrr_stat');
        $this->assertDatabaseHas('dashboard_preferences', ['user_id' => $this->admin->id]);

        $hidden = collect($component->get('layout'))->firstWhere('id', 'mrr_stat');
        $this->assertFalse($hidden['visible']);

        // Adding a widget that isn't in the default layout appends it as visible.
        $component->call('toggleWidget', 'quick_actions');
        $added = collect($component->get('layout'))->firstWhere('id', 'quick_actions');
        $this->assertNotNull($added);
        $this->assertTrue($added['visible']);

        // Reset restores the default layout (mrr_stat visible again).
        $component->call('resetLayout');
        $reset = collect($component->get('layout'))->firstWhere('id', 'mrr_stat');
        $this->assertTrue($reset['visible']);
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
