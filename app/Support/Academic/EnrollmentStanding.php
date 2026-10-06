<?php

namespace App\Support\Academic;

use App\Enums\EnrollmentStatus;
use App\Enums\FinalStatus;

/**
 * Fotografia da situação de uma matrícula: pontos e faltas, interpretados
 * pelas regras acadêmicas da turma. Não consulta o banco; quem monta é o
 * EnrollmentStandingCalculator.
 *
 * Sem regras definidas ($rules = NULL), nada que dependa delas é calculado:
 * melhor não mostrar um resultado do que mostrar um resultado inventado.
 */
final readonly class EnrollmentStanding
{
    public function __construct(
        public ?AcademicRules $rules,
        public EnrollmentStatus $enrollmentStatus,
        public float $pointsEarned,
        public float $pointsDistributed,
        public int $absences,
        public int $classesLogged,
        public ?int $totalClasses,
    ) {}

    public function hasRules(): bool
    {
        return $this->rules !== null;
    }

    public function passingPoints(): ?float
    {
        return $this->rules?->passingPoints();
    }

    /**
     * Quanto falta para atingir a média; zero quando já atingiu.
     */
    public function pointsNeeded(): ?float
    {
        $passingPoints = $this->passingPoints();

        return $passingPoints === null ? null : max(0.0, round($passingPoints - $this->pointsEarned, 2));
    }

    /**
     * Pontos do semestre que ainda não foram distribuídos em avaliações.
     */
    public function pointsStillAvailable(): ?float
    {
        return $this->rules === null ? null : max(0.0, round($this->rules->totalPoints - $this->pointsDistributed, 2));
    }

    public function hasReachedPassingPoints(): bool
    {
        $passingPoints = $this->passingPoints();

        return $passingPoints !== null && $this->pointsEarned >= $passingPoints;
    }

    /**
     * Já não dá para atingir a média com os pontos que restam no período.
     */
    public function cannotReachPassingPoints(): bool
    {
        return $this->rules !== null && $this->pointsNeeded() > $this->pointsStillAvailable();
    }

    /**
     * Aproveitamento nas avaliações já corrigidas, de 0 a 100.
     */
    public function performancePercent(): ?float
    {
        return $this->pointsDistributed > 0 ? round($this->pointsEarned / $this->pointsDistributed * 100, 1) : null;
    }

    /**
     * Limite de faltas; NULL sem regras ou enquanto o estudante não informar
     * o total de aulas previstas (sem ele, qualquer limite seria um chute).
     */
    public function allowedAbsences(): ?int
    {
        return $this->rules === null || $this->totalClasses === null
            ? null
            : $this->rules->allowedAbsences($this->totalClasses);
    }

    public function absencesLeft(): ?int
    {
        $allowed = $this->allowedAbsences();

        return $allowed === null ? null : max(0, $allowed - $this->absences);
    }

    public function hasExceededAbsences(): bool
    {
        $allowed = $this->allowedAbsences();

        return $allowed !== null && $this->absences > $allowed;
    }

    /**
     * Reprovação por falta vale a qualquer momento (é definitiva). Aprovação
     * ou reprovação por nota só quando o estudante conclui a matrícula e as
     * regras são conhecidas.
     */
    public function finalStatus(): FinalStatus
    {
        if ($this->hasExceededAbsences()) {
            return FinalStatus::FailedAbsence;
        }

        if ($this->enrollmentStatus !== EnrollmentStatus::Completed || $this->rules === null) {
            return FinalStatus::InProgress;
        }

        return $this->hasReachedPassingPoints() ? FinalStatus::Approved : FinalStatus::Failed;
    }
}
