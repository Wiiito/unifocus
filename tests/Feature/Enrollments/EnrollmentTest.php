<?php

namespace Tests\Feature\Enrollments;

use App\Enums\EnrollmentStatus;
use App\Enums\FinalStatus;
use App\Models\AcademicTerm;
use App\Models\ClassGroup;
use App\Models\Enrollment;
use App\Models\GradeEntry;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('enrollments.index'))->assertRedirect(route('login'));
    }

    public function test_enrolling_creates_a_personal_class_group_owned_by_the_student(): void
    {
        $user = User::factory()->create();
        $subject = Subject::factory()->create();

        $response = $this->actingAs($user)->post(route('enrollments.store'), [
            'subject_id' => $subject->id,
            'room' => 'B12',
            'total_classes' => 72,
            'total_points' => 100,
            'passing_percent' => 65,
            'max_absence_percent' => 25,
            'schedule' => [
                ['weekday' => '1', 'start' => '19:00', 'end' => '20:40'],
                ['weekday' => '', 'start' => '', 'end' => ''],
            ],
        ]);

        $enrollment = $user->enrollments()->sole();
        $response->assertRedirect(route('enrollments.show', $enrollment));

        $classGroup = $enrollment->classGroup;
        $this->assertTrue($classGroup->is_personal);
        $this->assertSame($user->id, $classGroup->created_by_user_id);
        $this->assertSame($subject->id, $classGroup->subject_id);
        $this->assertSame(72, $classGroup->total_classes);
        /** assertEquals: o jsonb do Postgres não preserva a ordem das chaves. */
        $this->assertEquals([['weekday' => 1, 'start' => '19:00', 'end' => '20:40']], $classGroup->schedule);
    }

    public function test_approval_rules_are_required_when_no_institution_provides_them(): void
    {
        $user = User::factory()->create();
        $subject = Subject::factory()->create();

        $this->actingAs($user)
            ->post(route('enrollments.store'), ['subject_id' => $subject->id])
            ->assertSessionHasErrors(['total_points', 'passing_percent', 'max_absence_percent']);
    }

    public function test_approval_rules_are_optional_when_the_subject_belongs_to_an_institution(): void
    {
        $institution = Institution::factory()->create();
        $user = $this->memberOf($institution);
        $subject = Subject::factory()->for($institution)->create();

        $this->actingAs($user)
            ->post(route('enrollments.store'), ['subject_id' => $subject->id])
            ->assertSessionHasNoErrors();

        $this->assertNull($user->enrollments()->sole()->classGroup->total_points);
    }

    public function test_rejects_a_subject_from_an_institution_the_student_is_not_linked_to(): void
    {
        $user = User::factory()->create();
        $subject = Subject::factory()->for(Institution::factory())->create();

        $this->actingAs($user)
            ->post(route('enrollments.store'), ['subject_id' => $subject->id])
            ->assertSessionHasErrors('subject_id');

        $this->assertSame(0, Enrollment::count());
    }

    public function test_rejects_a_term_from_another_institution_than_the_subject(): void
    {
        $institution = Institution::factory()->create();
        $otherInstitution = Institution::factory()->create();
        $user = $this->memberOf($institution, $otherInstitution);
        $subject = Subject::factory()->for($institution)->create();
        $otherTerm = AcademicTerm::factory()->for($otherInstitution)->create();

        $this->actingAs($user)
            ->post(route('enrollments.store'), ['subject_id' => $subject->id, 'academic_term_id' => $otherTerm->id])
            ->assertSessionHasErrors(['academic_term_id' => __('O período precisa ser da mesma instituição da matéria.')]);
    }

    public function test_rejects_enrolling_twice_in_the_same_subject_and_term(): void
    {
        $institution = Institution::factory()->create();
        $user = $this->memberOf($institution);
        $subject = Subject::factory()->for($institution)->create();
        $term = AcademicTerm::factory()->for($institution)->create();
        $payload = ['subject_id' => $subject->id, 'academic_term_id' => $term->id];

        $this->actingAs($user)->post(route('enrollments.store'), $payload)->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->post(route('enrollments.store'), $payload)
            ->assertSessionHasErrors(['subject_id' => __('Você já está matriculado nesta matéria neste período.')]);

        $this->assertSame(1, $user->enrollments()->count());
    }

    public function test_another_students_enrollment_returns_404(): void
    {
        $enrollment = Enrollment::factory()->forUser(User::factory()->create())->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get(route('enrollments.show', $enrollment))->assertNotFound();
        $this->actingAs($intruder)->put(route('enrollments.update', $enrollment), ['status' => 'completed'])->assertNotFound();
        $this->actingAs($intruder)->delete(route('enrollments.destroy', $enrollment))->assertNotFound();

        $this->assertModelExists($enrollment);
    }

    public function test_owner_sees_the_enrollment_page(): void
    {
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->forUser($user)->create();

        $this->actingAs($user)
            ->get(route('enrollments.show', $enrollment))
            ->assertOk()
            ->assertSee($enrollment->subject()->name);
    }

    public function test_completing_the_enrollment_computes_the_final_result(): void
    {
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->forUser($user)->create();
        GradeEntry::factory()->for($enrollment)->create(['points' => 70, 'max_points' => 100]);

        $this->actingAs($user)
            ->put(route('enrollments.update', $enrollment), [
                'status' => EnrollmentStatus::Completed->value,
                'total_points' => 100,
                'passing_percent' => 60,
                'max_absence_percent' => 25,
            ])
            ->assertRedirect(route('enrollments.show', $enrollment));

        $enrollment->refresh();
        $this->assertSame(70.0, $enrollment->final_grade);
        $this->assertSame(FinalStatus::Approved, $enrollment->final_status);
    }

    public function test_removing_the_enrollment_deletes_its_personal_class_group(): void
    {
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->forUser($user)->create();

        $this->actingAs($user)->delete(route('enrollments.destroy', $enrollment))->assertRedirect(route('enrollments.index'));

        $this->assertModelMissing($enrollment);
        $this->assertSame(0, ClassGroup::withTrashed()->count());
    }

    private function memberOf(Institution ...$institutions): User
    {
        $user = User::factory()->create();

        foreach ($institutions as $institution) {
            $user->memberships()->create(['institution_id' => $institution->id]);
        }

        return $user;
    }
}
