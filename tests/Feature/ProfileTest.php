<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Company::settings();
    }

    protected function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('secret123'),
        ]);
    }

    protected function client(): Client
    {
        return Client::create([
            'name' => 'Acme', 'email' => 'acme@example.com',
            'password' => bcrypt('secret123'), 'portal_enabled' => true,
        ]);
    }

    // --- Admin ---

    public function test_admin_can_view_profile_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.profile'))
            ->assertOk()
            ->assertSee('Profile Details');
    }

    public function test_admin_can_update_profile_details(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.profile.update'), [
                'name' => 'New Name', 'email' => 'new@example.com',
                'phone' => '0771234567', 'job_title' => 'Director',
            ])
            ->assertSessionHas('success');

        $admin->refresh();
        $this->assertSame('New Name', $admin->name);
        $this->assertSame('new@example.com', $admin->email);
        $this->assertSame('Director', $admin->job_title);
    }

    public function test_admin_can_upload_avatar(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.profile.update'), [
                'name' => $admin->name, 'email' => $admin->email,
                'avatar' => UploadedFile::fake()->image('me.jpg'),
            ])
            ->assertSessionHas('success');

        $admin->refresh();
        $this->assertNotNull($admin->avatar);
        Storage::disk('public')->assertExists($admin->avatar);
    }

    public function test_admin_can_change_password_with_correct_current(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.profile.password'), [
                'current_password' => 'secret123',
                'password' => 'newpassword1', 'password_confirmation' => 'newpassword1',
            ])
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('newpassword1', $admin->refresh()->password));
    }

    public function test_admin_password_change_rejects_wrong_current(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.profile.password'), [
                'current_password' => 'wrong',
                'password' => 'newpassword1', 'password_confirmation' => 'newpassword1',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('secret123', $admin->refresh()->password));
    }

    // --- Portal ---

    public function test_client_can_update_profile_but_not_email(): void
    {
        $client = $this->client();

        $this->actingAs($client, 'client')
            ->put(route('portal.profile.update'), [
                'name' => 'Acme Corp', 'email' => 'hacker@evil.com',
                'phone' => '0712223334', 'address' => '123 Main St',
            ])
            ->assertSessionHas('success');

        $client->refresh();
        $this->assertSame('Acme Corp', $client->name);
        $this->assertSame('123 Main St', $client->address);
        $this->assertSame('acme@example.com', $client->email); // unchanged
    }

    public function test_client_can_change_password(): void
    {
        $client = $this->client();

        $this->actingAs($client, 'client')
            ->put(route('portal.profile.password'), [
                'current_password' => 'secret123',
                'password' => 'newpassword1', 'password_confirmation' => 'newpassword1',
            ])
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('newpassword1', $client->refresh()->password));
    }

    public function test_client_password_change_rejects_wrong_current(): void
    {
        $client = $this->client();

        $this->actingAs($client, 'client')
            ->put(route('portal.profile.password'), [
                'current_password' => 'wrong',
                'password' => 'newpassword1', 'password_confirmation' => 'newpassword1',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_guest_cannot_access_admin_profile(): void
    {
        $this->get(route('admin.profile'))->assertRedirect();
    }

    public function test_guest_cannot_access_portal_profile(): void
    {
        $this->get(route('portal.profile'))->assertRedirect();
    }
}
