<?php

namespace App\Contracts;

use App\Models\Enrollment;

/**
 * Implementado por todo model cuja alteração muda a situação de uma matrícula
 * (pontos, faltas ou resultado). O EnrollmentStandingObserver usa este
 * contrato para recalcular só as matrículas afetadas, sem precisar conhecer
 * cada model.
 */
interface AffectsEnrollmentStanding
{
    /**
     * @return iterable<int, Enrollment>
     */
    public function enrollmentsToRecalculate(): iterable;
}
