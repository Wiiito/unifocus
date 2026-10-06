<?php

namespace App\Contracts;

use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Implementado por todo model cuja alteração muda o progresso dos desafios
 * diários de alguém (presença, aula, resposta a questão). O
 * DailyChallengeObserver usa este contrato para reavaliar só os dias afetados.
 */
interface AffectsDailyChallenges
{
    /**
     * @return iterable<int, array{user: User, day: CarbonInterface}>
     */
    public function dailyChallengeDays(): iterable;
}
