<?php

namespace App\Models;

use App\Contracts\AffectsEnrollmentStanding;
use App\Enums\ActivityType;
use App\Observers\EnrollmentStandingObserver;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Atividade/avaliação da turma. max_points é quanto ela distribui no
 * semestre (ex.: prova valendo 30 pontos).
 */
#[Fillable(['class_group_id', 'lesson_id', 'created_by_user_id', 'title', 'description', 'type', 'max_points', 'weight', 'available_from', 'due_at', 'allow_late'])]
#[ObservedBy(EnrollmentStandingObserver::class)]
class Activity extends Model implements AffectsEnrollmentStanding
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory, SoftDeletes;

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
            'type' => ActivityType::class,
            'max_points' => 'float',
            'weight' => 'float',
            'available_from' => 'datetime',
            'due_at' => 'datetime',
            'allow_late' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ClassGroup, $this>
     */
    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    /**
     * @return BelongsTo<Lesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * @return HasMany<ActivitySubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(ActivitySubmission::class);
    }

    /**
     * @return HasMany<GradeEntry, $this>
     */
    public function gradeEntries(): HasMany
    {
        return $this->hasMany(GradeEntry::class);
    }

    public function isGraded(): bool
    {
        return $this->max_points !== null;
    }

    public function isOverdue(): bool
    {
        return $this->due_at?->isPast() ?? false;
    }

    /**
     * @return iterable<int, Enrollment>
     */
    public function enrollmentsToRecalculate(): iterable
    {
        return $this->classGroup->enrollments()->get();
    }
}
