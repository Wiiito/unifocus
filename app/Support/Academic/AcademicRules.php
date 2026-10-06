<?php

namespace App\Support\Academic;

use App\Models\ClassGroup;

/**
 * Regras de aprovação que valem para uma turma. Cada regra vem da própria
 * turma (definida pelo estudante) ou, se ela não definir, da instituição.
 * Não há padrão global: sem turma nem instituição, a regra é desconhecida.
 */
final readonly class AcademicRules
{
    public function __construct(
        public int $totalPoints,
        public float $passingPercent,
        public int $maxAbsencePercent,
    ) {}

    /**
     * NULL quando alguma regra não pôde ser resolvida (ex.: matéria do
     * catálogo geral sem regras na turma, ou instituição removida).
     */
    public static function forClassGroup(ClassGroup $classGroup): ?self
    {
        $institution = $classGroup->institution();

        $totalPoints = $classGroup->total_points ?? $institution?->total_points;
        $passingPercent = $classGroup->passing_percent ?? $institution?->passing_percent;
        $maxAbsencePercent = $classGroup->max_absence_percent ?? $institution?->max_absence_percent;

        if ($totalPoints === null || $passingPercent === null || $maxAbsencePercent === null) {
            return null;
        }

        return new self($totalPoints, $passingPercent, $maxAbsencePercent);
    }

    /**
     * Pontos mínimos para aprovação (ex.: 65% de 100 = 65).
     */
    public function passingPoints(): float
    {
        return round($this->totalPoints * $this->passingPercent / 100, 2);
    }

    /**
     * Quantas aulas o estudante pode faltar em uma turma com o total informado.
     */
    public function allowedAbsences(int $totalClasses): int
    {
        return (int) floor($totalClasses * $this->maxAbsencePercent / 100);
    }
}
