<?php

namespace App\Actions\Enrollments;

use App\Models\Enrollment;
use App\Support\Academic\EnrollmentStandingCalculator;

/**
 * Atualiza os caches da matrícula (pontos, faltas e resultado) a partir das
 * notas e presenças. Único lugar que escreve nessas colunas.
 */
class RecalculateEnrollmentStanding
{
    public function __construct(private EnrollmentStandingCalculator $calculator) {}

    public function handle(Enrollment $enrollment): void
    {
        $standing = $this->calculator->calculate($enrollment);

        $enrollment->forceFill([
            'final_grade' => $standing->pointsDistributed > 0 ? $standing->pointsEarned : null,
            'absence_count' => $standing->absences,
            'final_status' => $standing->finalStatus(),
        ]);

        /** Quietly: salvar os caches não pode disparar um novo recálculo. */
        $enrollment->saveQuietly();
    }
}
