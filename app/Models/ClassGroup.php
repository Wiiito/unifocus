<?php

namespace App\Models;

use App\Contracts\AffectsEnrollmentStanding;
use App\Enums\ClassGroupStatus;
use App\Observers\EnrollmentStandingObserver;
use Database\Factories\ClassGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Turma = matéria + período + pessoas. Hoje toda turma é pessoal (criada pelo
 * estudante ao se matricular); turmas compartilhadas chegam com a integração.
 */
#[Fillable(['subject_id', 'academic_term_id', 'created_by_user_id', 'name', 'is_personal', 'schedule', 'room', 'total_classes', 'total_points', 'passing_percent', 'max_absence_percent', 'status'])]
#[ObservedBy(EnrollmentStandingObserver::class)]
class ClassGroup extends Model implements AffectsEnrollmentStanding
{
    /** @use HasFactory<ClassGroupFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_personal' => 'boolean',
            'schedule' => 'array',
            'total_classes' => 'integer',
            'total_points' => 'integer',
            'passing_percent' => 'float',
            'max_absence_percent' => 'integer',
            'status' => ClassGroupStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class)->withTrashed();
    }

    /**
     * @return BelongsTo<AcademicTerm, $this>
     */
    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return HasMany<Lesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * @return HasMany<Activity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * A instituição vem do período (que é sempre institucional) ou, sem
     * período, da própria matéria. Não é copiada para a turma para não divergir.
     */
    public function institution(): ?Institution
    {
        return $this->academicTerm?->institution ?? $this->subject?->institution;
    }

    /**
     * @return iterable<int, Enrollment>
     */
    public function enrollmentsToRecalculate(): iterable
    {
        return $this->enrollments()->get();
    }
}
