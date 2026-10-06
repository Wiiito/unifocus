<?php

namespace Tests\Feature\Enrollments;

use App\Enums\GradeKind;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\GradeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_standalone_grade_adds_to_the_enrollment_points(): void
    {
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->forUser($user)->create();

        $this->actingAs($user)
            ->post(route('enrollments.grade-entries.store', $enrollment), [
                'label' => 'Participação',
                'kind' => GradeKind::Manual->value,
                'points' => 5,
                'max_points' => 10,
            ])
            ->assertRedirect(route('enrollments.show', $enrollment).'#notas');

        $grade = $enrollment->gradeEntries()->sole();
        $this->assertNull($grade->activity_id);
        $this->assertSame(1.0, $grade->weight);
        $this->assertSame(5.0, $enrollment->fresh()->final_grade);
    }

    public function test_weighted_standalone_recovery_counts_double(): void
    {
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->forUser($user)->create();

        $this->actingAs($user)
            ->post(route('enrollments.grade-entries.store', $enrollment), [
                'label' => 'Recuperação final',
                'kind' => GradeKind::Recovery->value,
                'points' => 7,
                'max_points' => 10,
                'weight' => 2,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(14.0, $enrollment->fresh()->final_grade);
    }

    public function test_activity_kind_is_not_accepted_for_a_standalone_grade(): void
    {
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->forUser($user)->create();

        $this->actingAs($user)
            ->post(route('enrollments.grade-entries.store', $enrollment), [
                'label' => 'Nota',
                'kind' => GradeKind::Activity->value,
                'points' => 5,
                'max_points' => 10,
            ])
            ->assertSessionHasErrors('kind');
    }

    public function test_activity_grade_cannot_be_edited_as_standalone_grade(): void
    {
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->forUser($user)->create();
        $gradeEntry = GradeEntry::factory()
            ->for($enrollment)
            ->for(Activity::factory()->for($enrollment->classGroup))
            ->create();

        $this->actingAs($user)
            ->get(route('enrollments.grade-entries.edit', [$enrollment, $gradeEntry]))
            ->assertNotFound();
    }

    public function test_grade_of_another_enrollment_returns_404(): void
    {
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->forUser($user)->create();
        $foreignGrade = GradeEntry::factory()->create();

        $this->actingAs($user)
            ->delete(route('enrollments.grade-entries.destroy', [$enrollment, $foreignGrade]))
            ->assertNotFound();

        $this->assertModelExists($foreignGrade);
    }
}
