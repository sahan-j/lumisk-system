<?php

namespace Tests\Feature;

use App\Livewire\Admin\Tickets\TicketShow as AdminTicketShow;
use App\Livewire\Portal\Tickets\TicketCreate;
use App\Mail\TicketCreatedAdminMail;
use App\Mail\TicketReplyMail;
use App\Models\Client;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TicketSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings()->update(['email' => 'support@lumisk.test', 'ticket_notifications_enabled' => true]);
        $this->admin = User::factory()->create();
        $this->client = Client::create([
            'name' => 'Acme Ltd', 'email' => 'hi@acme.com', 'portal_enabled' => true, 'password' => bcrypt('secret123'),
        ]);
    }

    public function test_client_creates_ticket_with_sla_and_notifies_admin(): void
    {
        Mail::fake();
        Storage::fake('public');

        Livewire::actingAs($this->client, 'client')
            ->test(TicketCreate::class)
            ->set('subject', 'Site is down')
            ->set('type', 'bug_report')
            ->set('priority', 'high')
            ->set('message', 'The homepage returns a 500 error.')
            ->set('attachments', [UploadedFile::fake()->create('log.pdf', 100, 'application/pdf')])
            ->call('store');

        $ticket = Ticket::first();
        $this->assertNotNull($ticket);
        $this->assertSame('TKT-001', $ticket->ticket_number);
        $this->assertSame('open', $ticket->status);
        $this->assertNotNull($ticket->sla_due_at);
        // High priority default SLA = 4 hours from now.
        $this->assertEqualsWithDelta(now()->addHours(4)->timestamp, $ticket->sla_due_at->timestamp, 60);
        $this->assertSame(1, $ticket->messages()->count());
        $this->assertSame(1, $ticket->attachments()->count());

        Mail::assertSent(TicketCreatedAdminMail::class);
    }

    public function test_admin_reply_sets_waiting_client_and_first_response(): void
    {
        Mail::fake();
        $ticket = $this->makeTicket();

        Livewire::actingAs($this->admin)
            ->test(AdminTicketShow::class, ['ticket' => $ticket])
            ->set('replyMessage', 'We are looking into it.')
            ->set('isInternalNote', false)
            ->call('reply');

        $ticket->refresh();
        $this->assertSame('waiting_client', $ticket->status);
        $this->assertNotNull($ticket->first_response_at);
        Mail::assertSent(TicketReplyMail::class);
    }

    public function test_internal_note_is_not_emailed_and_hidden_from_portal(): void
    {
        Mail::fake();
        $ticket = $this->makeTicket();

        Livewire::actingAs($this->admin)
            ->test(AdminTicketShow::class, ['ticket' => $ticket])
            ->set('replyMessage', 'Customer is on free plan — deprioritise.')
            ->set('isInternalNote', true)
            ->call('reply');

        Mail::assertNothingSent();

        // Portal must not see the internal note.
        $this->actingAs($this->client, 'client')
            ->get(route('portal.tickets.show', $ticket))
            ->assertOk()
            ->assertDontSee('deprioritise');
    }

    public function test_admin_first_view_moves_open_ticket_to_in_progress(): void
    {
        $ticket = $this->makeTicket();
        $this->assertSame('open', $ticket->status);

        Livewire::actingAs($this->admin)->test(AdminTicketShow::class, ['ticket' => $ticket]);

        $this->assertSame('in_progress', $ticket->fresh()->status);
    }

    public function test_sla_command_flags_overdue_tickets(): void
    {
        $ticket = $this->makeTicket();
        $ticket->update(['sla_due_at' => now()->subHour(), 'is_overdue_sla' => false]);

        $this->artisan('tickets:check-sla')->assertSuccessful();

        $this->assertTrue($ticket->fresh()->is_overdue_sla);
    }

    public function test_client_cannot_view_another_clients_ticket(): void
    {
        $other = Client::create(['name' => 'Other', 'email' => 'o@x.com', 'portal_enabled' => true, 'password' => bcrypt('secret123')]);
        $ticket = $this->makeTicket($other);

        $this->actingAs($this->client, 'client')
            ->get(route('portal.tickets.show', $ticket))
            ->assertNotFound();
    }

    private function makeTicket(?Client $client = null): Ticket
    {
        $client ??= $this->client;

        $ticket = Ticket::create([
            'ticket_number' => Ticket::generateNumber(),
            'subject' => 'Need help',
            'type' => 'general_question',
            'status' => 'open',
            'priority' => 'medium',
            'client_id' => $client->id,
            'sla_due_at' => now()->addHours(24),
        ]);

        $ticket->messages()->create([
            'sender_type' => 'client',
            'sender_name' => $client->name,
            'message' => 'Initial message',
            'is_internal_note' => false,
        ]);

        return $ticket;
    }
}
