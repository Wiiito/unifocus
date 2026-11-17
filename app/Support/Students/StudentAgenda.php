<?php

namespace App\Support\Students;

use App\Enums\ActivityType;
use App\Enums\LessonStatus;
use App\Enums\SubmissionStatus;
use App\Models\AcademicTermEvent;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Monta a agenda do estudante a partir das matrículas em andamento: prazos de
 * atividades ainda não entregues, aulas e marcos do calendário do período.
 */
class StudentAgenda
{
    /** Prazos dentro desta janela são destacados como urgentes. */
    private const URGENT_WITHIN_HOURS = 48;

    /**
     * @return Collection<int, AgendaItem>
     */
    public function between(User $user, CarbonInterface $from, CarbonInterface $until): Collection
    {
        return $this->deadlines($user, $from, $until)
            ->concat($this->lessons($user, $from, $until))
            ->concat($this->termEvents($user, $from, $until))
            ->sortBy(fn (AgendaItem $item) => $item->startsAt->getTimestamp())
            ->values();
    }

    /**
     * Atividades com prazo vencido que o estudante ainda não entregou.
     *
     * @return Collection<int, AgendaItem>
     */
    public function overdue(User $user): Collection
    {
        return $this->pendingActivities($user)
            ->where('due_at', '<', now())
            ->orderBy('due_at')
            ->get()
            ->map(fn (Activity $activity) => $this->deadlineItem($activity, $user, isUrgent: true));
    }

    /**
     * Dados completos de todas as matérias em andamento para o calendário
     * mensal e a aba de lembretes da página /agenda.
     *
     * @return array{
     *     subjects: array<int, array{id: int, name: string, color: string}>,
     *     activities: array<int, array<string, mixed>>,
     *     lessons: array<int, array<string, mixed>>,
     *     events: array<int, array<string, mixed>>,
     * }
     */
    public function calendarData(User $user): array
    {
        $enrollments = $user->enrollments()
            ->ongoing()
            ->withSummary()
            ->get();

        $subjects = $enrollments
            ->map(fn (Enrollment $enrollment): array => [
                'id' => $enrollment->subject()->id,
                'name' => $enrollment->subject()->name,
                'color' => $enrollment->subject()->displayColor(),
            ])
            ->unique('id')
            ->sortBy('name')
            ->values()
            ->all();

        $urgentCutoff = now()->addHours(self::URGENT_WITHIN_HOURS);
        $now = now();

        $activities = $this->pendingActivities($user)
            ->orderBy('due_at')
            ->get()
            ->map(function (Activity $activity) use ($user, $now, $urgentCutoff): array {
                $subject = $activity->classGroup->subject;
                $isOverdue = $activity->due_at->lt($now);
                $isUrgent = ! $isOverdue && $activity->due_at->lessThanOrEqualTo($urgentCutoff);
                $isExam = in_array($activity->type, [ActivityType::Exam, ActivityType::Recovery], true);

                return [
                    'id' => $activity->id,
                    'kind' => 'activity',
                    'category' => $isExam ? 'exam' : 'delivery',
                    'typeLabel' => $activity->type->label(),
                    'icon' => $activity->type->icon(),
                    'title' => $activity->title,
                    'subjectId' => $subject->id,
                    'subjectName' => $subject->name,
                    'subjectColor' => $subject->displayColor(),
                    'date' => $activity->due_at->format('Y-m-d'),
                    'dayNum' => $activity->due_at->format('d'),
                    'monthShort' => $activity->due_at->translatedFormat('M'),
                    'dateLabel' => $activity->due_at->translatedFormat('d/m'),
                    'timeLabel' => $activity->due_at->format('H:i'),
                    'isOverdue' => $isOverdue,
                    'isUrgent' => $isUrgent,
                    'statusLabel' => $isOverdue ? __('Atrasada') : ($isUrgent ? __('Urgente') : __('Pendente')),
                    'url' => route('enrollments.activities.edit', [$this->enrollmentIdFor($user, $activity->class_group_id), $activity]),
                ];
            })
            ->values()
            ->all();

        $today = today();

        $lessons = Lesson::query()
            ->whereIn('class_group_id', $this->ongoingClassGroupIds($user))
            ->where('status', '!=', LessonStatus::Canceled)
            ->with('classGroup.subject')
            ->orderBy('starts_at')
            ->get()
            ->map(function (Lesson $lesson) use ($user, $today): array {
                $subject = $lesson->classGroup->subject;
                $dayLabel = $lesson->starts_at->isToday()
                    ? __('Hoje')
                    : ($lesson->starts_at->isTomorrow() ? __('Amanhã') : $lesson->starts_at->translatedFormat('l'));

                return [
                    'id' => $lesson->id,
                    'kind' => 'lesson',
                    'category' => 'lesson',
                    'typeLabel' => __('Aula'),
                    'icon' => 'school',
                    'title' => $lesson->title,
                    'subjectId' => $subject->id,
                    'subjectName' => $subject->name,
                    'subjectColor' => $subject->displayColor(),
                    'date' => $lesson->starts_at->format('Y-m-d'),
                    'dayLabel' => $dayLabel,
                    'dateLabel' => $lesson->starts_at->format('d/m'),
                    'timeLabel' => $lesson->starts_at->format('H:i'),
                    'isPast' => $lesson->starts_at->lt($today),
                    'url' => route('enrollments.lessons.edit', [$this->enrollmentIdFor($user, $lesson->class_group_id), $lesson]),
                ];
            })
            ->values()
            ->all();

        $termIds = $enrollments
            ->pluck('classGroup.academic_term_id')
            ->filter()
            ->unique()
            ->values();

        $events = AcademicTermEvent::query()
            ->whereIn('academic_term_id', $termIds)
            ->with('academicTerm.institution')
            ->orderBy('starts_on')
            ->get()
            ->map(fn (AcademicTermEvent $event): array => [
                'id' => $event->id,
                'kind' => 'event',
                'category' => 'event',
                'typeLabel' => $event->type->label(),
                'icon' => $event->type->icon(),
                'title' => $event->title,
                'institutionName' => $event->academicTerm->institution->name,
                'termName' => $event->academicTerm->name,
                'startsOn' => $event->starts_on->format('Y-m-d'),
                'endsOn' => $event->ends_on->format('Y-m-d'),
                'rangeLabel' => $event->starts_on->format('d/m')
                    .(! $event->ends_on->isSameDay($event->starts_on) ? ' – '.$event->ends_on->format('d/m') : ''),
                'isOngoing' => $today->betweenIncluded($event->starts_on, $event->ends_on),
                'isPast' => $event->ends_on->lt($today),
            ])
            ->values()
            ->all();

        return [
            'subjects' => $subjects,
            'activities' => $activities,
            'lessons' => $lessons,
            'events' => $events,
        ];
    }

    /**
     * Prazos de atividades ainda não entregues dentro do intervalo. Também
     * usado pelos lembretes push (notifications:deadlines).
     *
     * @return Collection<int, AgendaItem>
     */
    public function deadlines(User $user, CarbonInterface $from, CarbonInterface $until): Collection
    {
        return $this->pendingActivities($user)
            ->whereBetween('due_at', [$from, $until])
            ->get()
            ->map(fn (Activity $activity) => $this->deadlineItem(
                $activity,
                $user,
                isUrgent: $activity->due_at->lessThanOrEqualTo(now()->addHours(self::URGENT_WITHIN_HOURS)),
            ));
    }

    /**
     * @return Collection<int, AgendaItem>
     */
    private function lessons(User $user, CarbonInterface $from, CarbonInterface $until): Collection
    {
        return Lesson::query()
            ->whereIn('class_group_id', $this->ongoingClassGroupIds($user))
            ->where('status', '!=', LessonStatus::Canceled)
            ->whereBetween('starts_at', [$from, $until])
            ->with('classGroup.subject')
            ->get()
            ->map(fn (Lesson $lesson) => new AgendaItem(
                kind: AgendaItem::KIND_LESSON,
                title: $lesson->title,
                context: $lesson->classGroup->subject->name,
                startsAt: $lesson->starts_at,
                icon: 'school',
                url: route('enrollments.lessons.edit', [$this->enrollmentIdFor($user, $lesson->class_group_id), $lesson]),
                color: $lesson->classGroup->subject->displayColor(),
            ));
    }

    /**
     * @return Collection<int, AgendaItem>
     */
    private function termEvents(User $user, CarbonInterface $from, CarbonInterface $until): Collection
    {
        $termIds = $user->enrollments()->ongoing()
            ->join('class_groups', 'class_groups.id', '=', 'enrollments.class_group_id')
            ->whereNotNull('class_groups.academic_term_id')
            ->distinct()
            ->pluck('class_groups.academic_term_id');

        return AcademicTermEvent::query()
            ->whereIn('academic_term_id', $termIds)
            ->whereDate('ends_on', '>=', $from)
            ->whereDate('starts_on', '<=', $until)
            ->with('academicTerm.institution')
            ->get()
            ->map(fn (AcademicTermEvent $event) => new AgendaItem(
                kind: AgendaItem::KIND_TERM_EVENT,
                title: $event->title,
                context: $event->type->label().' · '.$event->academicTerm->institution->name,
                startsAt: $event->starts_on->max($from->copy()->startOfDay()),
                icon: 'event',
                isAllDay: true,
            ));
    }

    /**
     * @return Builder<Activity>
     */
    private function pendingActivities(User $user): Builder
    {
        return Activity::query()
            ->whereIn('class_group_id', $this->ongoingClassGroupIds($user))
            ->whereNotNull('due_at')
            ->whereDoesntHave('submissions', fn (Builder $query) => $query
                ->whereIn('enrollment_id', $user->enrollments()->select('id'))
                ->where('status', '!=', SubmissionStatus::Pending))
            ->with('classGroup.subject');
    }

    private function deadlineItem(Activity $activity, User $user, bool $isUrgent): AgendaItem
    {
        return new AgendaItem(
            kind: AgendaItem::KIND_DEADLINE,
            title: $activity->title,
            context: $activity->classGroup->subject->name.' · '.$activity->type->label(),
            startsAt: $activity->due_at,
            icon: $activity->type->icon(),
            url: route('enrollments.activities.edit', [$this->enrollmentIdFor($user, $activity->class_group_id), $activity]),
            color: $activity->classGroup->subject->displayColor(),
            isUrgent: $isUrgent,
        );
    }

    /**
     * Turma em andamento -> matrícula do estudante nela (memorizado: monta
     * os links de todos os itens com uma consulta só).
     *
     * @return Collection<int, int>
     */
    private function ongoingEnrollments(User $user): Collection
    {
        return once(fn () => $user->enrollments()->ongoing()->pluck('id', 'class_group_id'));
    }

    /**
     * @return array<int, int>
     */
    private function ongoingClassGroupIds(User $user): array
    {
        return $this->ongoingEnrollments($user)->keys()->all();
    }

    private function enrollmentIdFor(User $user, int $classGroupId): int
    {
        return $this->ongoingEnrollments($user)->get($classGroupId);
    }
}
