<?php

namespace Tests\Feature\Console\Commands\Notifications;

use App\Enums\EnrollmentStatus;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\UserStreak;
use App\Notifications\DeadlinesApproaching;
use App\Notifications\StreakAtRisk;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendReminderCommandsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_deadline_reminder_lists_undelivered_activities_due_in_the_next_24_hours(): void
    {
        $this->travelTo(now()->setTime(8, 0));
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->forUser($user)->create();
        Activity::factory()->for($enrollment->classGroup)->create(['title' => 'Prova 1', 'due_at' => now()->addHours(6)]);
        Activity::factory()->for($enrollment->classGroup)->create(['title' => 'Lista 2', 'due_at' => now()->addHours(20)]);
        Activity::factory()->for($enrollment->classGroup)->create(['title' => 'Semana que vem', 'due_at' => now()->addDays(3)]);
        $delivered = Activity::factory()->for($enrollment->classGroup)->create(['title' => 'Entregue', 'due_at' => now()->addHours(3)]);
        $delivered->submissions()->create(['enrollment_id' => $enrollment->id, 'status' => 'submitted']);
        Notification::fake();

        $this->artisan('notifications:deadlines')->assertSuccessful();

        Notification::assertSentTo($user, DeadlinesApproaching::class, function (DeadlinesApproaching $notification) use ($user): bool {
            $message = $notification->toArray($user);

            $this->assertSame('2 prazos nas próximas 24 horas', $message['title']);
            $this->assertSame('Prova 1 (hoje às 14:00) e Lista 2 (amanhã às 04:00)', $message['body']);

            return true;
        });
    }

    public function test_no_deadline_reminder_without_pending_deadlines_or_for_dropped_enrollments(): void
    {
        $idle = User::factory()->create();
        Enrollment::factory()->forUser($idle)->create();
        $dropped = User::factory()->create();
        $droppedEnrollment = Enrollment::factory()->forUser($dropped)->create(['status' => EnrollmentStatus::Dropped]);
        Activity::factory()->for($droppedEnrollment->classGroup)->create(['due_at' => now()->addHours(5)]);
        Notification::fake();

        $this->artisan('notifications:deadlines')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_streak_reminder_only_for_streaks_that_go_out_tonight(): void
    {
        $atRisk = $this->streak(days: 4, lastCompletedOn: today()->subDay());
        $doneToday = $this->streak(days: 5, lastCompletedOn: today());
        $alreadyOut = $this->streak(days: 3, lastCompletedOn: today()->subDays(2));
        Notification::fake();

        $this->artisan('notifications:streaks')->assertSuccessful();

        Notification::assertSentTo($atRisk, StreakAtRisk::class, fn (StreakAtRisk $notification) => $notification->streakDays === 4);
        Notification::assertNotSentTo([$doneToday, $alreadyOut], StreakAtRisk::class);
    }

    private function streak(int $days, mixed $lastCompletedOn): User
    {
        $user = User::factory()->create();
        UserStreak::create(['user_id' => $user->id, 'current_count' => $days, 'longest_count' => $days, 'last_completed_on' => $lastCompletedOn]);

        return $user;
    }
}
