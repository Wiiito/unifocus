<?php

namespace App\Models;

use App\Contracts\AffectsEnrollmentStanding;
use App\Enums\AttendanceStatus;
use App\Models\Concerns\Auditable;
use App\Observers\EnrollmentStandingObserver;
use Database\Factories\LessonAttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Presença de uma matrícula em uma aula, com quem lançou.
 */
#[Fillable(['lesson_id', 'enrollment_id', 'status', 'justification', 'recorded_by_user_id', 'recorded_at'])]
#[ObservedBy(EnrollmentStandingObserver::class)]
class LessonAttendance extends Model implements AffectsEnrollmentStanding
{
    /** @use HasFactory<LessonAttendanceFactory> */
    use Auditable, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AttendanceStatus::class,
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Lesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * @return BelongsTo<Enrollment, $this>
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * @return iterable<int, Enrollment>
     */
    public function enrollmentsToRecalculate(): iterable
    {
        return [$this->enrollment];
    }
}
