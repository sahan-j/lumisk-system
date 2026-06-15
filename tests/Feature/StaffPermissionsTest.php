<?php

namespace Tests\Feature;

use App\Livewire\Admin\Staff\StaffForm;
use App\Livewire\Admin\Staff\StaffIndex;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class StaffPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
        $this->seed(PermissionsSeeder::class);
    }

    private function makeUser(string $role, bool $active = true): User
    {
        return User::create([
            'name' => ucfirst($role), 'email' => $role . fake()->unique()->numerify('##') . '@x.com',
            'password' => Hash::make('password123'), 'role' => $role, 'is_active' => $active,
        ]);
    }

    public function test_super_admin_has_every_permission(): void
    {
        $user = $this->makeUser('super_admin');
        $this->assertTrue($user->hasPermission('staff.delete'));
        $this->assertTrue($user->hasPermission('anything.at.all'));
    }

    public function test_staff_role_defaults(): void
    {
        $user = $this->makeUser('staff');
        $this->assertTrue($user->hasPermission('invoices.create'));
        $this->assertTrue($user->hasPermission('dashboard.view'));
        $this->assertFalse($user->hasPermission('reports.view'));
        $this->assertFalse($user->hasPermission('staff.view'));
        $this->assertFalse($user->hasPermission('invoices.delete'));
    }

    public function test_admin_excludes_staff_create_and_settings_edit(): void
    {
        $user = $this->makeUser('admin');
        $this->assertTrue($user->hasPermission('settings.view'));
        $this->assertTrue($user->hasPermission('staff.view'));
        $this->assertTrue($user->hasPermission('invoices.delete'));
        $this->assertFalse($user->hasPermission('settings.edit'));
        $this->assertFalse($user->hasPermission('staff.create'));
        $this->assertFalse($user->hasPermission('staff.delete'));
    }

    public function test_user_override_grants_and_revokes(): void
    {
        $user = $this->makeUser('staff');

        DB::table('user_permissions')->insert(['user_id' => $user->id, 'permission_name' => 'reports.view', 'granted' => true]);
        DB::table('user_permissions')->insert(['user_id' => $user->id, 'permission_name' => 'invoices.create', 'granted' => false]);
        $user->clearPermissionCache();

        $this->assertTrue($user->hasPermission('reports.view'));   // granted override
        $this->assertFalse($user->hasPermission('invoices.create')); // revoked override
    }

    public function test_route_middleware_blocks_unpermitted_section(): void
    {
        $staff = $this->makeUser('staff');

        $this->actingAs($staff)->get('/admin/dashboard')->assertOk();
        $this->actingAs($staff)->get('/admin/reports')->assertForbidden();
        $this->actingAs($staff)->get('/admin/staff')->assertForbidden();
    }

    public function test_override_opens_blocked_route(): void
    {
        $staff = $this->makeUser('staff');
        DB::table('user_permissions')->insert(['user_id' => $staff->id, 'permission_name' => 'reports.view', 'granted' => true]);

        $this->actingAs($staff)->get('/admin/reports')->assertOk();
    }

    public function test_inactive_user_is_logged_out_by_middleware(): void
    {
        $staff = $this->makeUser('staff', active: false);

        $this->actingAs($staff)->get('/admin/dashboard')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $this->makeUser('staff', active: false)->update(['email' => 'off@x.com']);

        $this->post('/admin/login', ['email' => 'off@x.com', 'password' => 'password123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_super_admin_creates_staff_with_override(): void
    {
        $super = $this->makeUser('super_admin');

        Livewire::actingAs($super)
            ->test(StaffForm::class)
            ->set('name', 'New Staffer')
            ->set('email', 'newstaff@x.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'staff')
            ->set('permState.reports_view', true) // grant beyond staff defaults
            ->call('save')
            ->assertRedirect(route('admin.staff.index'));

        $created = User::where('email', 'newstaff@x.com')->first();
        $this->assertNotNull($created);
        $this->assertSame('staff', $created->role);
        $this->assertSame($super->id, $created->created_by);
        $this->assertTrue($created->hasPermission('reports.view'));
        $this->assertDatabaseHas('user_permissions', [
            'user_id' => $created->id, 'permission_name' => 'reports.view', 'granted' => true,
        ]);
    }

    public function test_super_admin_account_cannot_be_deleted(): void
    {
        $super = $this->makeUser('super_admin');
        $other = $this->makeUser('super_admin');

        Livewire::actingAs($super)
            ->test(StaffIndex::class)
            ->call('confirmDelete', $other->id)
            ->call('delete');

        $this->assertDatabaseHas('users', ['id' => $other->id]);
    }
}
