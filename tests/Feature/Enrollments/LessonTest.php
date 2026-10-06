<?php

namespace Tests\Feature\Enrollments;

use App\Enums\AttendanceStatus;
use App\Enums\FinalStatus;
use App\Enums\LessonStatus;
use App\Models\ClassGroup;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_an_absence_counts_the_classes_of_the_lesson(): void
    {
        [$user, $enrollment] = $this->enrollment();

        $this->actingAs($user)
            ->post(route('enrollments.lessons.store', $enrollment), $this->payload([
                'class_count' => 2,
                'attendance_status' => AttendanceStatus::Absent->value,
            ]))
            ->assertRedirect(route('enrollments.show', $enrollment).'#aulas');

        $lesson = $enrollment->lessons()->sole();
        $this->assertSame(AttendanceStatus::Absent, $lesson->attendances()->sole()->status);
        $this->assertSame(2, $enrollment->fresh()->absence_count);
    }

    public function test_excused_absence_and_canceled_lessons_do_not_count(): void
    {
        [$user, $enrollment] = $this->enrollment();

        $this->actingAs($user)->post(route('enrollments.lessons.store', $enrollment), $this->payload([
            'attendance_status' => AttendanceStatus::Excused->value,
        ]));
        $this->actingAs($user)->post(route('enrollments.lessons.store', $enrollment), $this->payload([
            'status' => LessonStatus::Canceled->value,
            'attendance_status' => AttendanceStatus::Absent->value,
        ]));

        $this->assertSame(0, $enrollment->fresh()->absence_count);
    }

    public function test_exceeding_the_absence_limit_marks_the_enrollment_as_failed_by_absence(): void
    {
        /** 25% de 4 aulas = 1 falta permitida. */
        [$user, $enrollment] = $this->enrollment(totalClasses: 4);

        $this->actingAs($user)->post(route('enrollments.lessons.store', $enrollment), $this->payload([
            'class_count' => 2,
            'attendance_status' => AttendanceStatus::Absent->value,
        ]));

        $this->assertSame(FinalStatus::FailedAbsence, $enrollment->fresh()->final_status);
    }

    public function test_clearing_the_attendance_and_deleting_the_lesson_recalculate_absences(): void
    {
        [$user, $enrollment] = $this->enrollment();
        $this->actingAs($user)->post(route('enrollments.lessons.store', $enrollment), $this->payload([
            'attendance_status' => AttendanceStatus::Absent->value,
        ]));
        $lesson = $enrollment->lessons()->sole();

        $this->actingAs($user)->put(route('enrollments.lessons.update', [$enrollment, $lesson]), $this->payload(['attendance_status' => null]));
        $this->assertSame(0, $lesson->attendances()->count());

        $this->actingAs($user)->put(route('enrollments.lessons.update', [$enrollment, $lesson]), $this->payload([
            'attendance_status' => AttendanceStatus::Absent->value,
        ]));
        $this->assertSame(1, $enrollment->fresh()->absence_count);

        $this->actingAs($user)->delete(route('enrollments.lessons.destroy', [$enrollment, $lesson]));
        $this->assertSoftDeleted($lesson);
        $this->assertSame(0, $enrollment->fresh()->absence_count);
    }

    public function test_lesson_from_another_class_group_returns_404(): void
    {
        [$user, $enrollment] = $this->enrollment();
        $foreignLesson = Lesson::factory()->create();

        $this->actingAs($user)
            ->get(route('enrollments.lessons.edit', [$enrollment, $foreignLesson]))
            ->assertNotFound();
    }

    public function test_another_student_cannot_record_lessons_in_the_enrollment(): void
    {
        [, $enrollment] = $this->enrollment();

        $this->actingAs(User::factory()->create())
            ->post(route('enrollments.lessons.store', $enrollment), $this->payload())
            ->assertNotFound();

        $this->assertSame(0, Lesson::count());
    }

    public function test_title_and_date_are_required(): void
    {
        [$user, $enrollment] = $this->enrollment();

        $this->actingAs($user)
            ->post(route('enrollments.lessons.store', $enrollment), $this->payload(['title' => '', 'starts_at' => '']))
            ->assertSessionHasErrors(['title', 'starts_at']);
    }

    /**
     * @return array{0: User, 1: Enrollment}
     */
    private function enrollment(?int $totalClasses = 72): array
    {
        $user = User::factory()->create();
        $classGroup = ClassGroup::factory()->for($user, 'createdBy')->create(['total_classes' => $totalClasses]);

        return [$user, Enrollment::factory()->for($classGroup)->for($user)->create()];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'title' => 'Aula 1',
            'starts_at' => '2026-10-05 19:00',
            'class_count' => 1,
            'status' => LessonStatus::Done->value,
            'attendance_status' => AttendanceStatus::Present->value,
            ...$overrides,
        ];
    }
}
