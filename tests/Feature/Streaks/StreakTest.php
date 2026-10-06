<?php

namespace Tests\Feature\Streaks;

use App\Enums\AttendanceStatus;
use App\Enums\LessonStatus;
use App\Models\Admin;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonAttendance;
use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\User;
use App\Models\UserStreak;
use App\Support\Streaks\StreakService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StreakTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_the_three_challenges_through_the_app_lights_the_streak(): void
    {
        $this->travelTo(Carbon::parse('2026-10-06 10:00'));
        [$user, $enrollment] = $this->student();
        $question = Question::factory()->withOptions()->for($enrollment->subject())->create();

        $this->actingAs($user)->get(route('agenda'))->assertOk();
        $this->actingAs($user)->post(route('enrollments.lessons.store', $enrollment), [
            'title' => 'Aula 1',
            'starts_at' => '2026-10-06 08:00',
            'class_count' => 1,
            'status' => LessonStatus::Done->value,
            'attendance_status' => AttendanceStatus::Present->value,
        ]);
        foreach (range(1, 3) as $attempt) {
            $this->actingAs($user)->post(route('practice.store', $question), ['option_id' => $question->options()->first()->id]);
        }

        $progress = $user->dailyChallengeProgress()->sole();
        $this->assertSame(1, $progress->attended_lessons);
        $this->assertNotNull($progress->viewed_agenda_at);
        $this->assertSame(3, $progress->questions_answered);
        $this->assertNotNull($progress->completed_at);
        $this->assertSame(1, $user->streak->activeCount());
    }

    public function test_a_partially_completed_day_does_not_count(): void
    {
        $this->travelTo(Carbon::parse('2026-10-06 10:00'));
        [$user, $enrollment] = $this->student();

        $this->attend($enrollment, today());
        $this->answer($user, 2);
        app(StreakService::class)->recordAgendaView($user);

        $this->assertNull($user->dailyChallengeProgress()->sole()->completed_at);
        $this->assertNull($user->streak);
    }

    public function test_consecutive_days_build_the_streak_and_a_missed_day_puts_it_out(): void
    {
        [$user, $enrollment] = $this->student();

        $this->completeDay($user, $enrollment, Carbon::parse('2026-10-01'));
        $this->completeDay($user, $enrollment, Carbon::parse('2026-10-02'));
        $this->assertSame(2, $user->fresh()->streak->activeCount());

        /** Dia 3 em branco: no dia 4 o foguinho já está apagado. */
        $this->travelTo(Carbon::parse('2026-10-04 09:00'));
        $this->assertSame(0, $user->fresh()->streak->activeCount());

        $this->completeDay($user, $enrollment, Carbon::parse('2026-10-04'));
        $streak = $user->fresh()->streak;
        $this->assertSame(1, $streak->activeCount());
        $this->assertSame(2, $streak->longest_count);
    }

    public function test_streak_stays_lit_until_the_end_of_the_next_day(): void
    {
        [$user, $enrollment] = $this->student();
        $this->completeDay($user, $enrollment, Carbon::parse('2026-10-01'));

        $this->travelTo(Carbon::parse('2026-10-02 23:59'));

        $this->assertSame(1, $user->fresh()->streak->activeCount());
    }

    public function test_absence_or_canceled_lesson_does_not_count_as_attending(): void
    {
        $this->travelTo(Carbon::parse('2026-10-06 10:00'));
        [$user, $enrollment] = $this->student();

        $this->attend($enrollment, today(), AttendanceStatus::Absent);
        $this->attend($enrollment, today(), AttendanceStatus::Present, LessonStatus::Canceled);

        $this->assertSame(0, $user->dailyChallengeProgress()->sole()->attended_lessons);
    }

    public function test_changing_the_attendance_to_absent_undoes_the_day(): void
    {
        [$user, $enrollment] = $this->student();
        $attendance = $this->completeDay($user, $enrollment, Carbon::parse('2026-10-06'));

        $attendance->update(['status' => AttendanceStatus::Absent]);

        $this->assertNull($user->dailyChallengeProgress()->sole()->completed_at);
        $this->assertSame(0, $user->fresh()->streak->activeCount());
    }

    public function test_recording_yesterdays_lesson_today_completes_yesterday(): void
    {
        [$user, $enrollment] = $this->student();

        $this->travelTo(Carbon::parse('2026-10-05 20:00'));
        app(StreakService::class)->recordAgendaView($user);
        $this->answer($user, 3);

        $this->travelTo(Carbon::parse('2026-10-06 09:00'));
        $this->attend($enrollment, Carbon::parse('2026-10-05 19:00'));

        $streak = $user->fresh()->streak;
        $this->assertSame('2026-10-05', $streak->last_completed_on->toDateString());
        $this->assertSame(1, $streak->activeCount());
    }

    public function test_visiting_the_agenda_twice_keeps_the_first_visit(): void
    {
        $this->travelTo(Carbon::parse('2026-10-06 08:00'));
        [$user] = $this->student();

        $this->actingAs($user)->get(route('agenda'));
        $this->travelTo(Carbon::parse('2026-10-06 18:00'));
        $this->actingAs($user)->get(route('agenda'));

        $this->assertSame('08:00', $user->dailyChallengeProgress()->sole()->viewed_agenda_at->format('H:i'));
    }

    public function test_another_students_answers_do_not_count(): void
    {
        $this->travelTo(Carbon::parse('2026-10-06 10:00'));
        [$user] = $this->student();
        $this->answer(User::factory()->create(), 3);

        $this->assertSame(0, $user->dailyChallengeProgress()->count());
    }

    public function test_dashboard_shows_the_streak_and_todays_challenge_progress(): void
    {
        [$user, $enrollment] = $this->student();
        $this->completeDay($user, $enrollment, Carbon::parse('2026-10-05'));

        $this->travelTo(Carbon::parse('2026-10-06 10:00'));
        $this->answer($user, 2);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('Responder 3 questões'))
            ->assertSee('2/3', escape: false)
            ->assertSee('0/3 '.__('Completos'), escape: false)
            ->assertSee(__('1 dia de foguinho'));
    }

    public function test_admin_student_list_shows_each_students_streak(): void
    {
        $user = User::factory()->create(['name' => 'Ana Foguinho']);
        UserStreak::create(['user_id' => $user->id, 'current_count' => 7, 'longest_count' => 7, 'last_completed_on' => today()]);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get('/staff/users')
            ->assertOk()
            ->assertSee(__('7 dias de foguinho'));
    }

    /**
     * @return array{0: User, 1: Enrollment}
     */
    private function student(): array
    {
        $user = User::factory()->create();

        return [$user, Enrollment::factory()->forUser($user)->create()];
    }

    private function completeDay(User $user, Enrollment $enrollment, Carbon $day): LessonAttendance
    {
        $this->travelTo($day->copy()->setTime(10, 0));

        app(StreakService::class)->recordAgendaView($user);
        $this->answer($user, 3);

        return $this->attend($enrollment, $day->copy()->setTime(8, 0));
    }

    private function attend(Enrollment $enrollment, Carbon $startsAt, AttendanceStatus $status = AttendanceStatus::Present, LessonStatus $lessonStatus = LessonStatus::Done): LessonAttendance
    {
        $lesson = Lesson::factory()->for($enrollment->classGroup)->create(['starts_at' => $startsAt, 'status' => $lessonStatus]);

        return LessonAttendance::factory()->for($lesson)->for($enrollment)->create(['status' => $status]);
    }

    private function answer(User $user, int $count): void
    {
        QuestionAttempt::factory()->count($count)->for($user)->create(['answered_at' => now()]);
    }
}
