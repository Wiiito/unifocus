<?php

namespace App\Support\Academic;

use App\Enums\AttendanceStatus;
use App\Enums\LessonStatus;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Builder;

/**
 * Lê pontos e faltas de uma matrícula no banco e monta o EnrollmentStanding.
 * Pontos são ponderados: cada nota conta points × weight de max_points × weight.
 */
class EnrollmentStandingCalculator
{
    public function calculate(Enrollment $enrollment): EnrollmentStanding
    {
        $classGroup = $enrollment->classGroup;

        $grades = $enrollment->gradeEntries()
            ->where(fn (Builder $query) => $query->whereNull('activity_id')->orWhereHas('activity'))
            ->toBase()
            ->selectRaw('COALESCE(SUM(points * weight), 0) AS earned, COALESCE(SUM(max_points * weight), 0) AS distributed')
            ->first();

        $absences = (int) $enrollment->attendances()
            ->where('lesson_attendances.status', AttendanceStatus::Absent)
            ->join('lessons', 'lessons.id', '=', 'lesson_attendances.lesson_id')
            ->whereNull('lessons.deleted_at')
            ->where('lessons.status', '!=', LessonStatus::Canceled)
            ->sum('lessons.class_count');

        return new EnrollmentStanding(
            rules: AcademicRules::forClassGroup($classGroup),
            enrollmentStatus: $enrollment->status,
            pointsEarned: (float) $grades->earned,
            pointsDistributed: (float) $grades->distributed,
            absences: $absences,
            classesLogged: (int) $classGroup->lessons()->countable()->sum('class_count'),
            totalClasses: $classGroup->total_classes,
        );
    }
}
