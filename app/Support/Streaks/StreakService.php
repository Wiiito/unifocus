<?php

namespace App\Support\Streaks;

use App\Enums\AttendanceStatus;
use App\Models\DailyChallengeProgress;
use App\Models\LessonAttendance;
use App\Models\User;
use App\Models\UserStreak;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Ponto único do foguinho: registra o progresso dos desafios diários,
 * decide se o dia foi cumprido, recalcula a sequência e monta o quadro do
 * dia. Tudo que mostra ou altera o foguinho passa por aqui.
 *
 * Registrado como scoped: o quadro de cada usuário é calculado uma vez por
 * requisição, mesmo aparecendo no cabeçalho, na sidebar e no dashboard.
 */
class StreakService
{
    /** @var array<int, DailyChallengeBoard> */
    private array $boards = [];

    public function boardFor(User $user): DailyChallengeBoard
    {
        return $this->boards[$user->id] ??= new DailyChallengeBoard(
            $this->findProgress($user, today()),
            $user->streak()->first() ?? new UserStreak,
        );
    }

    /**
     * Desafio "ver o calendário": só conta a primeira visita do dia.
     */
    public function recordAgendaView(User $user): void
    {
        $progress = $this->progressFor($user, today());

        if ($progress->viewed_agenda_at !== null) {
            return;
        }

        $progress->viewed_agenda_at = now();
        $this->evaluate($user, $progress);
    }

    /**
     * Reavalia um dia a partir das fontes (presenças e respostas). Chamado
     * sempre que algo daquele dia muda, inclusive retroativamente.
     */
    public function refreshDay(User $user, CarbonInterface $day): void
    {
        $this->evaluate($user, $this->progressFor($user, $day));
    }

    private function evaluate(User $user, DailyChallengeProgress $progress): void
    {
        [$start, $end] = $this->dayBounds($progress->day);

        $progress->attended_lessons = $this->attendedLessons($user, $start, $end);
        $progress->questions_answered = $user->questionAttempts()->whereBetween('answered_at', [$start, $end])->count();

        $wasCompleted = $progress->completed_at !== null;
        $isCompleted = $progress->meetsAllChallenges();
        $progress->completed_at = $isCompleted ? ($progress->completed_at ?? now()) : null;
        $progress->save();

        if ($wasCompleted !== $isCompleted) {
            $this->recalculateStreak($user);
        }

        unset($this->boards[$user->id]);
    }

    /**
     * Sequência atual = dias cumpridos consecutivos terminando no último dia
     * cumprido; recorde = maior sequência já feita. Recalculado dos dias
     * gravados, então funciona igual para registros retroativos.
     */
    private function recalculateStreak(User $user): void
    {
        /** @var Collection<int, Carbon> $days */
        $days = $user->dailyChallengeProgress()
            ->whereNotNull('completed_at')
            ->orderBy('day')
            ->pluck('day')
            ->map(fn ($day) => Carbon::parse($day)->startOfDay())
            ->values();

        $longest = 0;
        $run = 0;
        $previous = null;

        foreach ($days as $day) {
            $run = $previous !== null && $previous->copy()->addDay()->isSameDay($day) ? $run + 1 : 1;
            $longest = max($longest, $run);
            $previous = $day;
        }

        $streak = UserStreak::updateOrCreate(['user_id' => $user->id], [
            'current_count' => $run,
            'longest_count' => $longest,
            'last_completed_on' => $previous?->toDateString(),
        ]);

        $user->setRelation('streak', $streak);
    }

    private function attendedLessons(User $user, CarbonInterface $start, CarbonInterface $end): int
    {
        return LessonAttendance::query()
            ->whereIn('enrollment_id', $user->enrollments()->select('id'))
            ->whereIn('status', AttendanceStatus::presences())
            ->whereHas('lesson', fn (Builder $query) => $query->countable()->whereBetween('starts_at', [$start, $end]))
            ->count();
    }

    private function progressFor(User $user, CarbonInterface $day): DailyChallengeProgress
    {
        return $this->findProgress($user, $day)
            ?? new DailyChallengeProgress(['user_id' => $user->id, 'day' => $day->toDateString()]);
    }

    /**
     * whereDate: o SQLite guarda a coluna date com horário, o Postgres não.
     */
    private function findProgress(User $user, CarbonInterface $day): ?DailyChallengeProgress
    {
        return $user->dailyChallengeProgress()->whereDate('day', $day->toDateString())->first();
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function dayBounds(CarbonInterface $day): array
    {
        $local = Carbon::parse($day->toDateString(), config('app.timezone'));

        return [$local->copy()->startOfDay(), $local->copy()->endOfDay()];
    }
}
