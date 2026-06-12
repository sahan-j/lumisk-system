<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
    }

    public function test_admin_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Admin Panel');
    }

    public function test_admin_can_log_in_and_reach_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@lumisktechnology.com',
            'password' => Hash::make('admin123'),
        ]);

        $this->post('/admin/login', [
            'email' => 'admin@lumisktechnology.com',
            'password' => 'admin123',
        ])->assertRedirect('/admin/dashboard');

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('Total Revenue');
    }

    public function test_portal_login_requires_portal_enabled(): void
    {
        Client::create([
            'name' => 'Disabled Client',
            'email' => 'off@example.com',
            'password' => Hash::make('secret123'),
            'portal_enabled' => false,
        ]);

        $this->post('/portal/login', [
            'email' => 'off@example.com',
            'password' => 'secret123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('client');
    }

    public function test_enabled_client_can_log_in(): void
    {
        $client = Client::create([
            'name' => 'Active Client',
            'email' => 'on@example.com',
            'password' => Hash::make('secret123'),
            'portal_enabled' => true,
        ]);

        $this->post('/portal/login', [
            'email' => 'on@example.com',
            'password' => 'secret123',
        ])->assertRedirect('/portal/dashboard');

        $this->actingAs($client, 'client')->get('/portal/dashboard')->assertOk()->assertSee('Welcome back');
    }

    public function test_admin_routes_require_auth(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }

    public function test_portal_routes_require_auth(): void
    {
        $this->get('/portal/dashboard')->assertRedirect('/portal/login');
    }
}
