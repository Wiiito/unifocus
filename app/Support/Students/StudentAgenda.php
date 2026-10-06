<?php

namespace App\Support\Students;

use App\Enums\LessonStatus;
use App\Enums\SubmissionStatus;
use App\Models\AcademicTermEvent;
use App\Models\Activity;
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
     * @return Collection<int, AgendaItem>
     */
    private function deadlines(User $user, CarbonInterface $from, CarbonInterface $until): Collection
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
