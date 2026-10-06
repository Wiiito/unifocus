<?php

namespace Tests\Feature\Livewire\Admin;

use App\Enums\EnrollmentStatus;
use App\Livewire\Admin\Dashboard;
use App\Models\Admin;
use App\Models\Enrollment;
use App\Models\QuestionAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_with_an_empty_database(): void
    {
        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Dashboard::class)
            ->assertOk();
    }

    public function test_shows_platform_statistics(): void
    {
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->forUser($user)->create();
        Enrollment::factory()->create(['status' => EnrollmentStatus::Dropped]);
        QuestionAttempt::factory()->for($user)->create(['is_correct' => true]);
        QuestionAttempt::factory()->for($user)->create(['is_correct' => false]);

        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Dashboard::class)
            ->assertViewHas('overview', fn (array $overview) => $overview['ongoing_enrollments'] === 1 && $overview['active_students'] === 1)
            ->assertViewHas('questionBank', fn (array $bank) => $bank['attempts'] === 2 && $bank['accuracy'] === 50.0)
            ->assertSee($enrollment->subject()->name);
    }
}
