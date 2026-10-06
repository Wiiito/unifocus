<?php

namespace App\Support\Admin;

use App\Enums\EnrollmentStatus;
use App\Enums\FinalStatus;
use App\Enums\ReviewStatus;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\GradeEntry;
use App\Models\Institution;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Números do painel admin. Cada método é uma consulta agregada (nada é
 * carregado linha a linha), para o dashboard continuar leve com a base cheia.
 */
class PlatformStatistics
{
    /**
     * @return array{users: int, new_users: int, active_students: int, institutions: int, subjects: int, ongoing_enrollments: int}
     */
    public function overview(int $days = 30): array
    {
        return [
            'users' => User::count(),
            'new_users' => User::where('created_at', '>=', now()->subDays($days))->count(),
            'active_students' => Enrollment::query()->ongoing()->distinct()->count('user_id'),
            'institutions' => Institution::count(),
            'subjects' => Subject::count(),
            'ongoing_enrollments' => Enrollment::query()->ongoing()->count(),
        ];
    }

    /**
     * O que os estudantes registram à mão: indica se a plataforma está em uso.
     *
     * @return array{lessons: int, activities: int, grade_entries: int, recent_records: int}
     */
    public function engagement(int $days = 30): array
    {
        $since = now()->subDays($days);

        return [
            'lessons' => Lesson::count(),
            'activities' => Activity::count(),
            'grade_entries' => GradeEntry::count(),
            'recent_records' => Lesson::where('created_at', '>=', $since)->count()
                + Activity::where('created_at', '>=', $since)->count()
                + GradeEntry::where('created_at', '>=', $since)->count(),
        ];
    }

    /**
     * @return array{approved: int, pending: int, rejected: int, attempts: int, accuracy: float|null}
     */
    public function questionBank(int $days = 30): array
    {
        $byStatus = Question::query()
            ->toBase()
            ->selectRaw('review_status, COUNT(*) AS total')
            ->groupBy('review_status')
            ->pluck('total', 'review_status');

        $attempts = QuestionAttempt::query()
            ->where('answered_at', '>=', now()->subDays($days))
            ->toBase()
            ->selectRaw('COUNT(*) AS total, AVG(CASE WHEN is_correct THEN 100.0 WHEN is_correct IS NOT NULL THEN 0 END) AS accuracy')
            ->first();

        return [
            'approved' => (int) ($byStatus[ReviewStatus::Approved->value] ?? 0),
            'pending' => (int) ($byStatus[ReviewStatus::Pending->value] ?? 0),
            'rejected' => (int) ($byStatus[ReviewStatus::Rejected->value] ?? 0),
            'attempts' => (int) $attempts->total,
            'accuracy' => $attempts->accuracy === null ? null : round((float) $attempts->accuracy, 1),
        ];
    }

    /**
     * Cadastros por dia, com os dias sem cadastro preenchidos com zero.
     *
     * @return Collection<int, array{date: Carbon, total: int}>
     */
    public function signupsPerDay(int $days = 30): Collection
    {
        $start = today()->subDays($days - 1);

        $totals = User::query()
            ->where('created_at', '>=', $start)
            ->toBase()
            ->selectRaw('DATE(created_at) AS day, COUNT(*) AS total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range(0, $days - 1))->map(function (int $offset) use ($start, $totals): array {
            $date = $start->copy()->addDays($offset);

            return ['date' => $date, 'total' => (int) ($totals[$date->toDateString()] ?? 0)];
        });
    }

    /**
     * Matérias mais cursadas no momento.
     *
     * @return EloquentCollection<int, Subject>
     */
    public function topSubjects(int $limit = 5): EloquentCollection
    {
        return Subject::query()
            ->withCount(['classGroups as ongoing_enrollments_count' => fn ($query) => $query
                ->join('enrollments', 'enrollments.class_group_id', '=', 'class_groups.id')
                ->where('enrollments.status', EnrollmentStatus::Active)])
            ->orderByDesc('ongoing_enrollments_count')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->filter(fn (Subject $subject) => $subject->ongoing_enrollments_count > 0)
            ->values();
    }

    /**
     * Distribuição dos resultados das matrículas, na ordem do enum.
     *
     * @return Collection<int, array{status: FinalStatus, total: int}>
     */
    public function outcomes(): Collection
    {
        $totals = Enrollment::query()
            ->toBase()
            ->selectRaw('final_status, COUNT(*) AS total')
            ->groupBy('final_status')
            ->pluck('total', 'final_status');

        return collect(FinalStatus::cases())->map(fn (FinalStatus $status): array => [
            'status' => $status,
            'total' => (int) ($totals[$status->value] ?? 0),
        ]);
    }

    /**
     * @return EloquentCollection<int, AuditLog>
     */
    public function recentActivity(int $limit = 8): EloquentCollection
    {
        return AuditLog::query()
            ->with('actor')
            ->latest('created_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}
