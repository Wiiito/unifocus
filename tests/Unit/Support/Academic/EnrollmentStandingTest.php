<?php

namespace Tests\Unit\Support\Academic;

use App\Enums\EnrollmentStatus;
use App\Enums\FinalStatus;
use App\Support\Academic\AcademicRules;
use App\Support\Academic\EnrollmentStanding;
use PHPUnit\Framework\TestCase;

class EnrollmentStandingTest extends TestCase
{
    public function test_completed_enrollment_with_passing_points_is_approved(): void
    {
        $standing = $this->standing(status: EnrollmentStatus::Completed, earned: 65);

        $this->assertSame(FinalStatus::Approved, $standing->finalStatus());
    }

    public function test_completed_enrollment_below_passing_points_fails(): void
    {
        $standing = $this->standing(status: EnrollmentStatus::Completed, earned: 64.99);

        $this->assertSame(FinalStatus::Failed, $standing->finalStatus());
    }

    public function test_active_enrollment_stays_in_progress_even_with_passing_points(): void
    {
        $standing = $this->standing(status: EnrollmentStatus::Active, earned: 90);

        $this->assertSame(FinalStatus::InProgress, $standing->finalStatus());
    }

    public function test_exceeding_the_absence_limit_fails_by_absence_before_completion(): void
    {
        /** 25% de 72 aulas = 18 faltas permitidas. */
        $standing = $this->standing(status: EnrollmentStatus::Active, earned: 90, absences: 19, totalClasses: 72);

        $this->assertSame(18, $standing->allowedAbsences());
        $this->assertSame(FinalStatus::FailedAbsence, $standing->finalStatus());
    }

    public function test_reaching_exactly_the_absence_limit_does_not_fail(): void
    {
        $standing = $this->standing(status: EnrollmentStatus::Active, absences: 18, totalClasses: 72);

        $this->assertSame(0, $standing->absencesLeft());
        $this->assertSame(FinalStatus::InProgress, $standing->finalStatus());
    }

    public function test_absence_limit_is_unknown_without_the_total_of_classes(): void
    {
        $standing = $this->standing(status: EnrollmentStatus::Active, absences: 40, totalClasses: null);

        $this->assertNull($standing->allowedAbsences());
        $this->assertSame(FinalStatus::InProgress, $standing->finalStatus());
    }

    public function test_completed_enrollment_without_rules_has_no_verdict(): void
    {
        $standing = $this->standing(status: EnrollmentStatus::Completed, earned: 90, rules: null);

        $this->assertNull($standing->pointsNeeded());
        $this->assertSame(FinalStatus::InProgress, $standing->finalStatus());
    }

    public function test_flags_when_remaining_points_cannot_reach_the_passing_mark(): void
    {
        /** Precisa de 65; tem 20 e só restam 30 a distribuir (70 já foram). */
        $standing = $this->standing(status: EnrollmentStatus::Active, earned: 20, distributed: 70);

        $this->assertSame(45.0, $standing->pointsNeeded());
        $this->assertSame(30.0, $standing->pointsStillAvailable());
        $this->assertTrue($standing->cannotReachPassingPoints());
    }

    private function standing(
        EnrollmentStatus $status,
        float $earned = 0,
        float $distributed = 100,
        int $absences = 0,
        ?int $totalClasses = 72,
        ?AcademicRules $rules = new AcademicRules(totalPoints: 100, passingPercent: 65, maxAbsencePercent: 25),
    ): EnrollmentStanding {
        return new EnrollmentStanding(
            rules: $rules,
            enrollmentStatus: $status,
            pointsEarned: $earned,
            pointsDistributed: $distributed,
            absences: $absences,
            classesLogged: 0,
            totalClasses: $totalClasses,
        );
    }
}
