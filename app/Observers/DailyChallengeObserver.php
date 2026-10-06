<?php

namespace App\Observers;

use App\Contracts\AffectsDailyChallenges;
use App\Support\Streaks\StreakService;

/**
 * Reavalia os desafios diários (e o foguinho) dos dias afetados sempre que
 * uma presença, aula ou resposta muda.
 */
class DailyChallengeObserver
{
    public function __construct(private StreakService $streaks) {}

    public function saved(AffectsDailyChallenges $model): void
    {
        $this->refresh($model);
    }

    public function deleted(AffectsDailyChallenges $model): void
    {
        $this->refresh($model);
    }

    public function restored(AffectsDailyChallenges $model): void
    {
        $this->refresh($model);
    }

    private function refresh(AffectsDailyChallenges $model): void
    {
        collect($model->dailyChallengeDays())
            ->unique(fn (array $target) => $target['user']->id.'@'.$target['day']->toDateString())
            ->each(fn (array $target) => $this->streaks->refreshDay($target['user'], $target['day']));
    }
}
