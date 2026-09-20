<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_receives_404_for_the_admin_subjects_page(): void
    {
        $response = $this->get('/staff/subjects');

        $response->assertNotFound();
    }

    public function test_authenticated_student_receives_404_for_the_admin_subjects_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/staff/subjects');

        $response->assertNotFound();
    }

    public function test_authenticated_admin_can_view_the_admin_subjects_page(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get('/staff/subjects');

        $response->assertOk();
        $response->assertSeeLivewire('admin.subjects.manager');
    }

    public function test_admin_login_page_redirects_authenticated_admins_away(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get('/staff/login');

        $response->assertRedirect(route('admin.subjects.index'));
    }
}
