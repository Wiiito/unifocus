<?php

namespace App\Support\Streaks;

use App\Enums\DailyChallenge;
use App\Models\DailyChallengeProgress;
use App\Models\UserStreak;
use Illuminate\Support\Collection;

/**
 * Quadro do dia de um estudante: desafios, meta diária e foguinho. Montado
 * pelo StreakService e consumido pelo cabeçalho, sidebar e dashboard.
 */
final readonly class DailyChallengeBoard
{
    /** @var Collection<int, ChallengeStatus> */
    public Collection $challenges;

    public function __construct(?DailyChallengeProgress $today, public UserStreak $streak)
    {
        $this->challenges = collect(DailyChallenge::cases())
            ->map(fn (DailyChallenge $challenge) => new ChallengeStatus($challenge, $challenge->progressIn($today)));
    }

    public function completedCount(): int
    {
        return $this->challenges->filter(fn (ChallengeStatus $status) => $status->isDone())->count();
    }

    public function total(): int
    {
        return $this->challenges->count();
    }

    public function percentage(): int
    {
        return (int) round($this->completedCount() / max(1, $this->total()) * 100);
    }

    public function isComplete(): bool
    {
        return $this->completedCount() === $this->total();
    }

    public function streakCount(): int
    {
        return $this->streak->activeCount();
    }
}
