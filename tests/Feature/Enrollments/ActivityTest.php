<?php

namespace Tests\Feature\Enrollments;

use App\Enums\GradeKind;
use App\Enums\SubmissionStatus;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\GradeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_grading_an_activity_records_the_grade_and_marks_it_as_graded(): void
    {
        [$user, $enrollment] = $this->enrollment();

        $this->actingAs($user)
            ->post(route('enrollments.activities.store', $enrollment), $this->payload([
                'type' => 'exam',
                'max_points' => 30,
                'points' => 25,
                'submission_status' => SubmissionStatus::Submitted->value,
            ]))
            ->assertRedirect(route('enrollments.show', $enrollment).'#atividades');

        $activity = $enrollment->activities()->sole();
        $grade = $enrollment->gradeEntries()->sole();
        $this->assertSame($activity->id, $grade->activity_id);
        $this->assertSame(GradeKind::Exam, $grade->kind);
        $this->assertSame(25.0, $grade->points);
        $this->assertSame(30.0, $grade->max_points);
        $this->assertSame(SubmissionStatus::Graded, $activity->submissions()->sole()->status);
        $this->assertSame(25.0, $enrollment->fresh()->final_grade);
    }

    public function test_weighted_recovery_counts_its_weight_in_the_total(): void
    {
        [$user, $enrollment] = $this->enrollment();

        /** Recuperação de 10 pontos com peso 2: 8 × 2 = 16 de 20 no total. */
        $this->actingAs($user)->post(route('enrollments.activities.store', $enrollment), $this->payload([
            'type' => 'recovery',
            'max_points' => 10,
            'points' => 8,
            'weight' => 2,
        ]));

        $grade = $enrollment->gradeEntries()->sole();
        $this->assertSame(GradeKind::Recovery, $grade->kind);
        $this->assertSame(2.0, $grade->weight);
        $this->assertSame(16.0, $enrollment->fresh()->final_grade);
    }

    public function test_blank_weight_defaults_to_one(): void
    {
        [$user, $enrollment] = $this->enrollment();

        $this->actingAs($user)
            ->post(route('enrollments.activities.store', $enrollment), $this->payload(['max_points' => 10, 'points' => 8, 'weight' => '']))
            ->assertSessionHasNoErrors();

        $this->assertSame(1.0, $enrollment->activities()->sole()->weight);
        $this->assertSame(8.0, $enrollment->fresh()->final_grade);
    }

    public function test_changing_the_activity_weight_updates_its_grade(): void
    {
        [$user, $enrollment] = $this->enrollment();
        $this->actingAs($user)->post(route('enrollments.activities.store', $enrollment), $this->payload(['max_points' => 10, 'points' => 8]));
        $activity = $enrollment->activities()->sole();

        $this->actingAs($user)->put(route('enrollments.activities.update', [$enrollment, $activity]), $this->payload([
            'max_points' => 10,
            'points' => 8,
            'weight' => 1.5,
        ]));

        $this->assertSame(1.5, $enrollment->gradeEntries()->sole()->weight);
        $this->assertSame(12.0, $enrollment->fresh()->final_grade);
    }

    public function test_weight_must_be_positive(): void
    {
        [$user, $enrollment] = $this->enrollment();

        $this->actingAs($user)
            ->post(route('enrollments.activities.store', $enrollment), $this->payload(['weight' => 0]))
            ->assertSessionHasErrors('weight');
    }

    public function test_pending_submission_has_no_delivery_date(): void
    {
        [$user, $enrollment] = $this->enrollment();

        $this->actingAs($user)->post(route('enrollments.activities.store', $enrollment), $this->payload());

        $submission = $enrollment->submissions()->sole();
        $this->assertSame(SubmissionStatus::Pending, $submission->status);
        $this->assertNull($submission->submitted_at);
    }

    public function test_clearing_the_points_removes_the_grade(): void
    {
        [$user, $enrollment] = $this->enrollment();
        $this->actingAs($user)->post(route('enrollments.activities.store', $enrollment), $this->payload(['max_points' => 10, 'points' => 8]));
        $activity = $enrollment->activities()->sole();

        $this->actingAs($user)->put(route('enrollments.activities.update', [$enrollment, $activity]), $this->payload([
            'max_points' => 10,
            'points' => null,
        ]));

        $this->assertSame(0, GradeEntry::count());
        $this->assertNull($enrollment->fresh()->final_grade);
    }

    public function test_points_cannot_exceed_the_activity_value(): void
    {
        [$user, $enrollment] = $this->enrollment();

        $this->actingAs($user)
            ->post(route('enrollments.activities.store', $enrollment), $this->payload(['max_points' => 10, 'points' => 11]))
            ->assertSessionHasErrors('points');

        $this->assertSame(0, Activity::count());
    }

    public function test_points_are_ignored_for_an_activity_without_value(): void
    {
        [$user, $enrollment] = $this->enrollment();

        $this->actingAs($user)
            ->post(route('enrollments.activities.store', $enrollment), $this->payload(['max_points' => null, 'points' => 5]))
            ->assertSessionHasNoErrors();

        $this->assertSame(0, GradeEntry::count());
    }

    public function test_deleting_the_activity_removes_its_points_from_the_total(): void
    {
        [$user, $enrollment] = $this->enrollment();
        $this->actingAs($user)->post(route('enrollments.activities.store', $enrollment), $this->payload(['max_points' => 10, 'points' => 8]));
        $activity = $enrollment->activities()->sole();

        $this->actingAs($user)->delete(route('enrollments.activities.destroy', [$enrollment, $activity]));

        $this->assertSoftDeleted($activity);
        $this->assertNull($enrollment->fresh()->final_grade);
    }

    public function test_changing_a_grade_is_audited_with_the_student_as_author(): void
    {
        [$user, $enrollment] = $this->enrollment();
        $this->actingAs($user)->post(route('enrollments.activities.store', $enrollment), $this->payload(['max_points' => 10, 'points' => 7]));
        $activity = $enrollment->activities()->sole();

        $this->actingAs($user)->put(route('enrollments.activities.update', [$enrollment, $activity]), $this->payload([
            'max_points' => 10,
            'points' => 9,
        ]));

        $log = AuditLog::where('action', 'updated')->where('auditable_type', (new GradeEntry)->getMorphClass())->sole();
        $this->assertTrue($log->actor->is($user));
        $this->assertEquals(7, $log->old_values['points']);
        $this->assertEquals(9, $log->new_values['points']);
    }

    public function test_another_student_cannot_edit_the_activity(): void
    {
        [, $enrollment] = $this->enrollment();
        $activity = Activity::factory()->for($enrollment->classGroup)->create();

        $this->actingAs(User::factory()->create())
            ->put(route('enrollments.activities.update', [$enrollment, $activity]), $this->payload())
            ->assertNotFound();
    }

    /**
     * @return array{0: User, 1: Enrollment}
     */
    private function enrollment(): array
    {
        $user = User::factory()->create();

        return [$user, Enrollment::factory()->forUser($user)->create()];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'title' => 'Lista 1',
            'type' => 'homework',
            'max_points' => null,
            'due_at' => '2026-10-10 23:59',
            'submission_status' => SubmissionStatus::Pending->value,
            ...$overrides,
        ];
    }
}
