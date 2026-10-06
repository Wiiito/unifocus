<?php

namespace App\Models;

use App\Contracts\AffectsDailyChallenges;
use App\Contracts\AffectsEnrollmentStanding;
use App\Enums\LessonStatus;
use App\Observers\DailyChallengeObserver;
use App\Observers\EnrollmentStandingObserver;
use Carbon\CarbonInterface;
use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Aula (encontro). class_count diz quantas aulas o encontro vale para a
 * contagem de faltas (aula dupla = 2).
 */
#[Fillable(['class_group_id', 'created_by_user_id', 'title', 'topic', 'description', 'starts_at', 'ends_at', 'class_count', 'status'])]
#[ObservedBy([EnrollmentStandingObserver::class, DailyChallengeObserver::class])]
class Lesson extends Model implements AffectsDailyChallenges, AffectsEnrollmentStanding
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'class_count' => 'integer',
            'status' => LessonStatus::class,
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
     * @return HasMany<LessonAttendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(LessonAttendance::class);
    }

    /**
     * Aulas que contam para frequência (canceladas não contam).
     */
    #[Scope]
    protected function countable(Builder $query): void
    {
        $query->where('status', '!=', LessonStatus::Canceled);
    }

    /**
     * @return iterable<int, Enrollment>
     */
    public function enrollmentsToRecalculate(): iterable
    {
        return $this->classGroup->enrollments()->get();
    }

    /**
     * Mudar data, situação ou excluir a aula afeta o dia antigo e o novo de
     * quem tem presença registrada nela.
     *
     * @return iterable<int, array{user: User, day: CarbonInterface}>
     */
    public function dailyChallengeDays(): iterable
    {
        $days = array_filter([$this->starts_at, $this->getOriginal('starts_at')]);

        return $this->attendances()->with('enrollment.user')->get()
            ->flatMap(fn (LessonAttendance $attendance) => array_map(
                fn ($day) => ['user' => $attendance->enrollment->user, 'day' => $day],
                $days,
            ))
            ->all();
    }
}
