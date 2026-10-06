<?php

namespace App\Models;

use App\Contracts\AffectsEnrollmentStanding;
use App\Enums\EnrollmentStatus;
use App\Enums\FinalStatus;
use App\Observers\EnrollmentStandingObserver;
use App\Support\Academic\AcademicRules;
use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Matrícula usuário <-> turma: o eixo da vida acadêmica do estudante.
 * Presença, entregas e notas apontam para cá.
 *
 * final_grade, final_status e absence_count são caches mantidos pelo
 * RecalculateEnrollmentStanding; nunca são editados pela interface.
 */
#[Fillable(['class_group_id', 'user_id', 'status', 'enrolled_at'])]
#[ObservedBy(EnrollmentStandingObserver::class)]
class Enrollment extends Model implements AffectsEnrollmentStanding
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'active',
        'final_status' => 'in_progress',
        'absence_count' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'final_grade' => 'float',
            'final_status' => FinalStatus::class,
            'absence_count' => 'integer',
            'enrolled_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Aulas da turma desta matrícula. Definida a partir da matrícula para que
     * as rotas aninhadas (/enrollments/{enrollment}/lessons/{lesson}) façam o
     * scoped binding sem consulta extra.
     *
     * @return HasMany<Lesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'class_group_id', 'class_group_id');
    }

    /**
     * @return HasMany<Activity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'class_group_id', 'class_group_id');
    }

    /**
     * @return HasMany<LessonAttendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(LessonAttendance::class);
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

    /**
     * Matrículas que o estudante ainda está cursando.
     */
    #[Scope]
    protected function ongoing(Builder $query): void
    {
        /** Qualificada: as consultas de agenda/prática juntam class_groups, que também tem status. */
        $query->where($query->qualifyColumn('status'), EnrollmentStatus::Active);
    }

    /**
     * Relações necessárias para exibir o resumo da matrícula (matéria,
     * período e as regras da instituição) sem N+1.
     */
    #[Scope]
    protected function withSummary(Builder $query): void
    {
        $query->with([
            'classGroup.subject.institution',
            'classGroup.academicTerm.institution',
        ]);
    }

    public function subject(): Subject
    {
        return $this->classGroup->subject;
    }

    /**
     * NULL quando as regras de aprovação não estão definidas.
     */
    public function academicRules(): ?AcademicRules
    {
        return AcademicRules::forClassGroup($this->classGroup);
    }

    /**
     * Limite de faltas a partir dos caches, sem consultar o banco.
     */
    public function allowedAbsences(): ?int
    {
        $totalClasses = $this->classGroup->total_classes;

        return $totalClasses === null ? null : $this->academicRules()?->allowedAbsences($totalClasses);
    }

    /**
     * @return iterable<int, Enrollment>
     */
    public function enrollmentsToRecalculate(): iterable
    {
        return [$this];
    }
}
