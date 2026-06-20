<?php

namespace Tests\Feature;

use App\Livewire\Admin\Leads\LeadForm;
use App\Livewire\Admin\Leads\LeadShow;
use App\Livewire\Admin\Pipeline\PipelineBoard;
use App\Models\Client;
use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\PipelineStage;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\PipelineStagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LeadPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
        $this->seed(PermissionsSeeder::class);
        $this->seed(PipelineStagesSeeder::class);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin' . fake()->unique()->numerify('##') . '@x.com',
            'password' => Hash::make('password123'), 'role' => 'super_admin', 'is_active' => true,
        ]);
    }

    private function firstStage(): PipelineStage
    {
        return PipelineStage::orderBy('sort_order')->first();
    }

    public function test_seeder_creates_seven_default_stages_with_one_won_and_one_lost(): void
    {
        $this->assertSame(7, PipelineStage::count());
        $this->assertSame(1, PipelineStage::where('is_won', true)->count());
        $this->assertSame(1, PipelineStage::where('is_lost', true)->count());
    }

    public function test_creating_a_lead_logs_an_activity_and_places_it_in_the_stage(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(LeadForm::class)
            ->set('name', 'Nimal Perera')
            ->set('company_name', 'Perera Holdings')
            ->set('value', 50000)
            ->set('probability', 60)
            ->set('source', 'referral')
            ->set('stage_id', $this->firstStage()->id)
            ->call('save')
            ->assertHasNoErrors();

        $lead = Lead::first();
        $this->assertSame('Nimal Perera', $lead->name);
        $this->assertSame($this->firstStage()->id, $lead->stage_id);
        $this->assertNotNull($lead->last_activity_at);
        $this->assertDatabaseHas('lead_activities', ['lead_id' => $lead->id, 'content' => 'Lead created.']);
    }

    public function test_weighted_value_accessor(): void
    {
        $lead = Lead::create([
            'name' => 'X', 'source' => 'website', 'stage_id' => $this->firstStage()->id,
            'value' => 100000, 'probability' => 25, 'currency' => 'LKR',
        ]);

        $this->assertEqualsWithDelta(25000, $lead->weighted_value, 0.001);
    }

    public function test_moving_a_lead_changes_stage_and_logs_transition(): void
    {
        $this->actingAs($this->admin());

        $from = $this->firstStage();
        $to = PipelineStage::where('id', '!=', $from->id)->where('is_won', false)->where('is_lost', false)->first();

        $lead = Lead::create([
            'name' => 'Mover', 'source' => 'website', 'stage_id' => $from->id,
            'probability' => 50, 'currency' => 'LKR',
        ]);

        Livewire::test(PipelineBoard::class)
            ->call('moveLead', $lead->id, $to->id, [$lead->id]);

        $this->assertSame($to->id, $lead->fresh()->stage_id);
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id, 'type' => 'stage_change',
        ]);
    }

    public function test_dropping_on_won_stage_prompts_to_convert(): void
    {
        $this->actingAs($this->admin());

        $won = PipelineStage::where('is_won', true)->first();
        $lead = Lead::create([
            'name' => 'Closer', 'source' => 'website', 'stage_id' => $this->firstStage()->id,
            'probability' => 80, 'currency' => 'LKR',
        ]);

        Livewire::test(PipelineBoard::class)
            ->call('moveLead', $lead->id, $won->id, [$lead->id])
            ->assertSet('confirmConvertLeadId', $lead->id);
    }

    public function test_converting_a_lead_creates_a_client_and_marks_converted(): void
    {
        $this->actingAs($this->admin());

        $lead = Lead::create([
            'name' => 'Sunil Fernando', 'company_name' => 'SF Ltd', 'email' => 'sunil@sf.com',
            'source' => 'referral', 'stage_id' => $this->firstStage()->id, 'probability' => 90, 'currency' => 'LKR',
        ]);

        Livewire::test(LeadShow::class, ['lead' => $lead])
            ->call('convert')
            ->assertRedirect();

        $client = Client::where('email', 'sunil@sf.com')->first();
        $this->assertNotNull($client);
        $this->assertSame('SF Ltd', $client->company_name);

        $lead->refresh();
        $this->assertSame($client->id, $lead->converted_to_client_id);
        $this->assertTrue($lead->is_converted);
        $this->assertNotNull($lead->converted_at);
        $this->assertTrue(PipelineStage::find($lead->stage_id)->is_won);
    }

    public function test_convert_uses_placeholder_email_when_lead_has_none(): void
    {
        $this->actingAs($this->admin());

        $lead = Lead::create([
            'name' => 'No Email', 'source' => 'walk_in', 'stage_id' => $this->firstStage()->id,
            'probability' => 50, 'currency' => 'LKR',
        ]);

        $client = $lead->convertToClient();
        $this->assertSame('lead-' . $lead->id . '@placeholder.com', $client->email);
    }

    public function test_marking_a_lead_lost_moves_it_to_lost_stage_with_reason(): void
    {
        $this->actingAs($this->admin());

        $lead = Lead::create([
            'name' => 'Gone', 'source' => 'website', 'stage_id' => $this->firstStage()->id,
            'probability' => 30, 'currency' => 'LKR',
        ]);

        Livewire::test(LeadShow::class, ['lead' => $lead])
            ->set('lostReason', 'Chose a competitor')
            ->call('markLost');

        $lead->refresh();
        $this->assertSame('Chose a competitor', $lead->lost_reason);
        $this->assertTrue(PipelineStage::find($lead->stage_id)->is_lost);
    }

    public function test_adding_an_activity_appends_to_the_timeline(): void
    {
        $this->actingAs($this->admin());

        $lead = Lead::create([
            'name' => 'Talker', 'source' => 'website', 'stage_id' => $this->firstStage()->id,
            'probability' => 50, 'currency' => 'LKR',
        ]);

        Livewire::test(LeadShow::class, ['lead' => $lead])
            ->set('activityType', 'call')
            ->set('activityContent', 'Spoke about pricing.')
            ->call('addActivity')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id, 'type' => 'call', 'content' => 'Spoke about pricing.',
        ]);
    }

    public function test_cannot_delete_a_stage_that_has_leads(): void
    {
        $this->actingAs($this->admin());

        $stage = $this->firstStage();
        Lead::create([
            'name' => 'Blocker', 'source' => 'website', 'stage_id' => $stage->id,
            'probability' => 50, 'currency' => 'LKR',
        ]);

        Livewire::test(PipelineBoard::class)->call('deleteStage', $stage->id);

        $this->assertDatabaseHas('pipeline_stages', ['id' => $stage->id]);
    }

    public function test_pipeline_view_loads(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.pipeline.index'))
            ->assertOk()
            ->assertSee('Sales Pipeline');
    }
}
