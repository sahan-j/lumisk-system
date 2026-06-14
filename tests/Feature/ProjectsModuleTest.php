<?php

namespace Tests\Feature;

use App\Livewire\Admin\Projects\ProjectForm;
use App\Livewire\Admin\Projects\ProjectShow;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use App\Services\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectsModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
        $this->admin = User::factory()->create();
        $this->client = Client::create([
            'name' => 'Acme Ltd', 'email' => 'hi@acme.com', 'portal_enabled' => true, 'password' => bcrypt('secret123'),
        ]);
    }

    public function test_admin_can_create_a_project_with_linked_invoice(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => DocumentNumberService::nextInvoiceNumber(),
            'client_id' => $this->client->id, 'status' => 'sent',
            'issue_date' => now(), 'due_date' => now()->addDays(14),
            'subtotal' => 1000, 'total' => 1000,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ProjectForm::class)
            ->set('name', 'Website Build')
            ->set('client_id', $this->client->id)
            ->set('status', 'active')
            ->set('priority', 'high')
            ->set('invoice_ids', [$invoice->id])
            ->call('save');

        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertSame('Website Build', $project->name);
        $this->assertSame('active', $project->status);
        $this->assertTrue($project->invoices->contains($invoice));
    }

    public function test_completing_status_stamps_completed_at(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ProjectForm::class)
            ->set('name', 'Done Soon')
            ->set('status', 'completed')
            ->set('priority', 'low')
            ->call('save');

        $this->assertNotNull(Project::first()->completed_at);
    }

    public function test_tasks_drive_completion_percentage(): void
    {
        $project = Project::create(['name' => 'P1', 'status' => 'active', 'priority' => 'medium']);

        $component = Livewire::actingAs($this->admin)->test(ProjectShow::class, ['project' => $project]);

        $component->set('taskTitle', 'Task A')->set('taskPriority', 'medium')->call('addTask');
        $component->set('taskTitle', 'Task B')->set('taskPriority', 'medium')->call('addTask');

        $this->assertSame(0, $project->fresh()->completion_percentage);

        $firstTask = $project->tasks()->first();
        $component->call('toggleTask', $firstTask->id);

        $this->assertSame('done', $firstTask->fresh()->status);
        $this->assertSame(50, $project->fresh()->completion_percentage);
    }

    public function test_portal_client_cannot_view_other_clients_project(): void
    {
        $other = Client::create(['name' => 'Other', 'email' => 'o@x.com', 'portal_enabled' => true, 'password' => bcrypt('secret123')]);
        $project = Project::create(['name' => 'Secret', 'client_id' => $other->id, 'status' => 'active', 'priority' => 'low']);

        $this->actingAs($this->client, 'client')
            ->get(route('portal.projects.show', $project))
            ->assertNotFound();
    }

    public function test_portal_client_can_view_own_project(): void
    {
        $project = Project::create(['name' => 'Mine', 'client_id' => $this->client->id, 'status' => 'active', 'priority' => 'low']);

        $this->actingAs($this->client, 'client')
            ->get(route('portal.projects.show', $project))
            ->assertOk()
            ->assertSee('Mine');
    }
}
