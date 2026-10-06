<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function adminPages(): array
    {
        return [
            'dashboard' => ['/staff'],
            'institutions' => ['/staff/institutions'],
            'academic terms' => ['/staff/academic-terms'],
            'subjects' => ['/staff/subjects'],
            'questions' => ['/staff/questions'],
            'users' => ['/staff/users'],
        ];
    }

    #[DataProvider('adminPages')]
    public function test_student_receives_404_for_every_admin_page(string $uri): void
    {
        $this->actingAs(User::factory()->create())->get($uri)->assertNotFound();
    }

    #[DataProvider('adminPages')]
    public function test_admin_can_view_every_admin_page(string $uri): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')->get($uri)->assertOk();
    }
}
