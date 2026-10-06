<?php

namespace App\Support\Streaks;

use App\Enums\DailyChallenge;

/**
 * Situação de um desafio no dia, pronta para exibir.
 */
final readonly class ChallengeStatus
{
    public function __construct(
        public DailyChallenge $challenge,
        public int $progress,
    ) {}

    public function target(): int
    {
        return $this->challenge->target();
    }

    public function isDone(): bool
    {
        return $this->progress >= $this->target();
    }

    /**
     * Progresso limitado ao alvo (5 questões de 3 aparecem como 3/3).
     */
    public function displayProgress(): int
    {
        return min($this->progress, $this->target());
    }
}
