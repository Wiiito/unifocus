<?php

namespace App\Support\Students;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Escolhe questões aprovadas das matérias que o estudante está cursando,
 * priorizando as que ele ainda não respondeu.
 */
class PracticeQuestionPicker
{
    public function next(User $user, ?int $subjectId = null): ?Question
    {
        return $this->pool($user, $subjectId)
            ->withExists(['attempts as answered' => fn (Builder $query) => $query->where('user_id', $user->id)])
            ->orderBy('answered')
            ->inRandomOrder()
            ->first();
    }

    /**
     * Baralho de revisão (flashcards do dashboard): só questões com gabarito.
     *
     * @return Collection<int, Question>
     */
    public function deck(User $user, int $size = 5): Collection
    {
        return $this->pool($user)
            ->where(fn (Builder $query) => $query->has('correctOption')->orWhereNotNull('explanation'))
            ->with(['subject', 'correctOption'])
            ->inRandomOrder()
            ->limit($size)
            ->get();
    }

    /**
     * @return Builder<Question>
     */
    private function pool(User $user, ?int $subjectId = null): Builder
    {
        $subjectIds = $user->enrollments()->ongoing()
            ->join('class_groups', 'class_groups.id', '=', 'enrollments.class_group_id')
            ->select('class_groups.subject_id');

        return Question::query()
            ->approved()
            ->whereIn('subject_id', $subjectIds)
            ->when($subjectId, fn (Builder $query) => $query->where('subject_id', $subjectId));
    }
}
