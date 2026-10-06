<?php

namespace App\Models;

use App\Contracts\AffectsEnrollmentStanding;
use App\Enums\GradeKind;
use App\Models\Concerns\Auditable;
use App\Observers\EnrollmentStandingObserver;
use Database\Factories\GradeEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Nota de uma matrícula em uma avaliação, no modelo de pontos: 25 de 30 é
 * points = 25, max_points = 30. O peso multiplica os dois no total do
 * período (padrão 1).
 */
#[Fillable(['enrollment_id', 'activity_id', 'label', 'kind', 'points', 'max_points', 'weight', 'recorded_by_user_id', 'recorded_at'])]
#[ObservedBy(EnrollmentStandingObserver::class)]
class GradeEntry extends Model implements AffectsEnrollmentStanding
{
    /** @use HasFactory<GradeEntryFactory> */
    use Auditable, HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'weight' => 1,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => GradeKind::class,
            'points' => 'float',
            'max_points' => 'float',
            'weight' => 'float',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Enrollment, $this>
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Aproveitamento nesta avaliação, de 0 a 100.
     */
    public function percentage(): float
    {
        return $this->max_points > 0 ? round($this->points / $this->max_points * 100, 1) : 0.0;
    }

    /**
     * @return iterable<int, Enrollment>
     */
    public function enrollmentsToRecalculate(): iterable
    {
        return [$this->enrollment];
    }
}
