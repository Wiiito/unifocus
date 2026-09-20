<?php

namespace Tests\Feature\Admin\Auth;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/staff/login');

        $response->assertStatus(200);
    }

    public function test_admins_can_authenticate_using_the_login_screen(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->post('/staff/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $this->assertAuthenticated('admin');
        $response->assertRedirect(route('admin.subjects.index', absolute: false));
    }

    public function test_admins_can_not_authenticate_with_invalid_password(): void
    {
        $admin = Admin::factory()->create();

        $this->post('/staff/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest('admin');
    }

    public function test_admins_can_logout(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->post('/staff/logout');

        $this->assertGuest('admin');
        $response->assertRedirect(route('admin.login'));
    }
}
