<?php

namespace Tests\Feature\Seeders;

use App\Models\Question;
use App\Models\User;
use App\Support\Streaks\StreakService;
use Database\Seeders\StudentDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StudentDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Dias da semana diferentes: o foguinho não pode depender de a grade
     * cair nos dias anteriores (ex.: domingo vira aula de reposição).
     *
     * @return array<string, array{0: string}>
     */
    public static function seedingDates(): array
    {
        return [
            'tuesday' => ['2026-10-06 10:00'],
            'monday' => ['2026-10-05 10:00'],
            'thursday' => ['2026-10-08 10:00'],
        ];
    }

    #[DataProvider('seedingDates')]
    public function test_leaves_the_student_with_a_three_day_streak_and_todays_challenges_open(string $now): void
    {
        $this->travelTo(Carbon::parse($now));
        $user = User::factory()->create();

        app(StudentDemoSeeder::class)->seedFor($user);

        $board = app(StreakService::class)->boardFor($user->fresh());
        $this->assertSame(StudentDemoSeeder::STREAK_DAYS, $board->streakCount());
        $this->assertFalse($board->isComplete());
        $this->assertSame(5, $user->enrollments()->count());
        $this->assertGreaterThan(0, Question::count());
    }

    public function test_running_twice_does_not_duplicate_the_student_data(): void
    {
        $user = User::factory()->create();

        app(StudentDemoSeeder::class)->seedFor($user);
        app(StudentDemoSeeder::class)->seedFor($user);

        $this->assertSame(5, $user->enrollments()->count());
    }
}
