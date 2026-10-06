<?php

namespace Database\Seeders;

use App\Actions\Enrollments\EnrollInSubject;
use App\Actions\Enrollments\RecalculateEnrollmentStanding;
use App\Actions\Enrollments\SaveActivity;
use App\Actions\Enrollments\SaveLesson;
use App\Actions\Questions\AnswerQuestion;
use App\Enums\AttendanceStatus;
use App\Enums\GradeKind;
use App\Enums\LessonStatus;
use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Enums\SubmissionStatus;
use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Institution;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use App\Support\Streaks\StreakService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Vida acadêmica de demonstração para um estudante: matrículas no catálogo
 * de Computação, aulas com presença, atividades com notas, respostas a
 * questões e um foguinho de 3 dias (os três últimos dias completos).
 *
 * Tudo passa pelas actions da aplicação, então pontos, faltas e foguinho
 * são calculados pelo mesmo código de produção. Datas relativas a hoje.
 */
class StudentDemoSeeder extends Seeder
{
    /** Dias completos (terminando ontem): o foguinho resultante. */
    public const STREAK_DAYS = 3;

    /** Semanas de aulas já registradas antes de hoje. */
    private const WEEKS_OF_HISTORY = 3;

    /**
     * code => [dias da semana (0 = domingo), início, fim, tópicos das aulas]
     *
     * @var array<string, array{0: array<int, int>, 1: string, 2: string, 3: array<int, string>}>
     */
    private const ENROLLMENTS = [
        'INF102' => [[1, 3], '19:00', '20:40', ['Listas encadeadas', 'Pilhas e filas', 'Árvores binárias', 'Árvores AVL', 'Tabelas hash', 'Grafos']],
        'INF201' => [[2, 4], '19:00', '20:40', ['Modelo ER', 'Modelo relacional', 'Normalização', 'SQL: consultas', 'SQL: junções', 'Transações']],
        'INF202' => [[1, 5], '21:00', '22:40', ['Classes e objetos', 'Encapsulamento', 'Herança', 'Polimorfismo', 'Interfaces', 'SOLID']],
        'INF302' => [[3, 5], '19:00', '20:40', ['Processos', 'Threads', 'Escalonamento', 'Sincronização', 'Deadlock', 'Memória virtual']],
        'MAT201' => [[2, 6], '08:00', '09:40', ['Lógica proposicional', 'Quantificadores', 'Conjuntos', 'Indução', 'Combinatória', 'Relações']],
    ];

    public function __construct(
        private EnrollInSubject $enrollInSubject,
        private SaveLesson $saveLesson,
        private SaveActivity $saveActivity,
        private AnswerQuestion $answerQuestion,
        private RecalculateEnrollmentStanding $recalculateStanding,
        private StreakService $streaks,
    ) {}

    /**
     * Sem argumento, popula o usuário de teste do DatabaseSeeder.
     */
    public function run(): void
    {
        $user = User::firstWhere('email', 'test@example.com');

        if ($user !== null) {
            $this->seedFor($user);
        }
    }

    public function seedFor(User $user): void
    {
        if ($user->enrollments()->exists()) {
            $this->command?->warn("{$user->email} já tem matrículas; nada foi alterado.");

            return;
        }

        $institution = Institution::firstWhere('slug', CatalogSeeder::INSTITUTION_SLUG)
            ?? $this->seedCatalog();

        $term = AcademicTerm::query()
            ->where('institution_id', $institution->id)
            ->where('name', CatalogSeeder::currentTermName())
            ->firstOrFail();

        $user->memberships()->firstOrCreate(['institution_id' => $institution->id], [
            'role' => MembershipRole::Student,
            'status' => MembershipStatus::Active,
            'registration_code' => '2026'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
            'joined_at' => now(),
        ]);
        $user->unsetRelation('memberships');

        $streakDays = $this->streakDays();
        $enrollments = $this->enroll($user, $institution, $term);

        /** As respostas "voltam no tempo"; o relógio original é restaurado no fim. */
        $originalNow = Carbon::getTestNow();

        try {
            foreach ($enrollments as $code => $enrollment) {
                $this->seedLessons($enrollment, $code, $streakDays);
                $this->seedActivities($enrollment, $code);
            }

            $this->ensureLessonOnEachStreakDay($enrollments->first(), $streakDays);
            $this->seedPractice($user, $enrollments, $streakDays);
        } finally {
            Carbon::setTestNow($originalNow);
        }

        /** Reforça o recálculo mesmo quando os eventos estão desligados (db:seed). */
        $enrollments->each(fn (Enrollment $enrollment) => $this->recalculateStanding->handle($enrollment->fresh()));

        foreach ($this->touchedDays() as $day) {
            $this->streaks->refreshDay($user, $day);
        }
    }

    private function seedCatalog(): Institution
    {
        app()->call([app(CatalogSeeder::class), 'run']);

        return Institution::where('slug', CatalogSeeder::INSTITUTION_SLUG)->firstOrFail();
    }

    /**
     * @return Collection<string, Enrollment>
     */
    private function enroll(User $user, Institution $institution, AcademicTerm $term): Collection
    {
        return collect(self::ENROLLMENTS)->mapWithKeys(function (array $data, string $code) use ($user, $institution, $term) {
            [$weekdays, $start, $end] = $data;
            $subject = Subject::where('institution_id', $institution->id)->where('code', $code)->firstOrFail();

            $enrollment = $this->enrollInSubject->handle($user, [
                'subject_id' => $subject->id,
                'academic_term_id' => $term->id,
                'name' => 'Turma A',
                'room' => 'Bloco B, sala '.(100 + count($weekdays) * 10 + strlen($code)),
                'total_classes' => (int) round($subject->workload_hours / 1.5),
                'schedule' => array_map(fn (int $weekday) => ['weekday' => $weekday, 'start' => $start, 'end' => $end], $weekdays),
            ]);

            return [$code => $enrollment];
        });
    }

    /**
     * Aulas das últimas semanas (com presença) e da próxima (agendadas).
     * Algumas faltas ficam fora dos dias do foguinho.
     *
     * @param  Collection<int, Carbon>  $streakDays
     */
    private function seedLessons(Enrollment $enrollment, string $code, Collection $streakDays): void
    {
        [$weekdays, $start, , $topics] = self::ENROLLMENTS[$code];
        $day = today()->subWeeks(self::WEEKS_OF_HISTORY);
        $number = 0;

        while ($day->lessThanOrEqualTo(today()->addWeek())) {
            if (in_array($day->dayOfWeek, $weekdays, true)) {
                $number++;
                $isPast = $day->lessThan(today());
                $isAbsence = $isPast && $number % 5 === 0 && ! $streakDays->contains(fn (Carbon $streakDay) => $streakDay->isSameDay($day));

                $this->saveLesson->handle($enrollment, [
                    'title' => "Aula {$number}",
                    'topic' => $topics[($number - 1) % count($topics)],
                    'starts_at' => $day->copy()->setTimeFromTimeString($start),
                    'class_count' => 2,
                    'status' => $isPast ? LessonStatus::Done->value : LessonStatus::Scheduled->value,
                    'attendance_status' => $isPast ? ($isAbsence ? AttendanceStatus::Absent->value : AttendanceStatus::Present->value) : null,
                ]);
            }

            $day->addDay();
        }
    }

    /**
     * Os dias do foguinho precisam de uma aula assistida. Se a grade não cair
     * num deles (ex.: domingo), entra uma aula de reposição.
     *
     * @param  Collection<int, Carbon>  $streakDays
     */
    private function ensureLessonOnEachStreakDay(Enrollment $enrollment, Collection $streakDays): void
    {
        $scheduledWeekdays = collect(self::ENROLLMENTS)->flatMap(fn (array $data) => $data[0])->unique();

        foreach ($streakDays as $day) {
            if ($scheduledWeekdays->contains($day->dayOfWeek)) {
                continue;
            }

            $this->saveLesson->handle($enrollment, [
                'title' => 'Aula de reposição',
                'topic' => 'Revisão para a P1',
                'starts_at' => $day->copy()->setTime(9, 0),
                'class_count' => 2,
                'status' => LessonStatus::Done->value,
                'attendance_status' => AttendanceStatus::Present->value,
            ]);
        }
    }

    private function seedActivities(Enrollment $enrollment, string $code): void
    {
        $activities = [
            ['title' => 'Lista de exercícios 1', 'type' => 'homework', 'max_points' => 10, 'weight' => 1, 'due_at' => today()->subWeeks(2)->setTime(23, 59), 'submission_status' => SubmissionStatus::Graded->value, 'points' => 8.5],
            ['title' => 'Trabalho prático', 'type' => 'project', 'max_points' => 20, 'weight' => 1, 'due_at' => today()->subDays(4)->setTime(23, 59), 'submission_status' => SubmissionStatus::Graded->value, 'points' => 16],
            ['title' => 'Lista de exercícios 2', 'type' => 'homework', 'max_points' => 10, 'weight' => 1, 'due_at' => today()->addDays(2)->setTime(23, 59), 'submission_status' => SubmissionStatus::Pending->value, 'points' => null],
            ['title' => 'Prova 1', 'type' => 'exam', 'max_points' => 30, 'weight' => 1, 'due_at' => today()->addWeeks(2)->startOfWeek()->addDays(array_search($code, array_keys(self::ENROLLMENTS), true))->setTime(19, 0), 'submission_status' => SubmissionStatus::Pending->value, 'points' => null],
        ];

        foreach ($activities as $activity) {
            $this->saveActivity->handle($enrollment, [...$activity, 'description' => null]);
        }

        if ($code === 'MAT201') {
            $enrollment->gradeEntries()->create([
                'label' => 'Participação em sala',
                'kind' => GradeKind::Manual,
                'points' => 4,
                'max_points' => 5,
                'weight' => 1,
                'recorded_by_user_id' => $enrollment->user_id,
                'recorded_at' => now(),
            ]);
        }
    }

    /**
     * Respostas a questões nos dias do foguinho (3+ por dia, com visita à
     * agenda) e algumas soltas nos dias anteriores.
     *
     * @param  Collection<string, Enrollment>  $enrollments
     * @param  Collection<int, Carbon>  $streakDays
     */
    private function seedPractice(User $user, Collection $enrollments, Collection $streakDays): void
    {
        $questions = Question::query()
            ->approved()
            ->whereIn('subject_id', $enrollments->map(fn (Enrollment $enrollment) => $enrollment->classGroup->subject_id))
            ->with('options')
            ->orderBy('id')
            ->get();

        $practiceDays = $streakDays->concat([today()->subDays(self::STREAK_DAYS + 3), today()->subDays(self::STREAK_DAYS + 5)]);

        foreach ($practiceDays->values() as $dayIndex => $day) {
            $isStreakDay = $streakDays->contains($day);

            Carbon::setTestNow($day->copy()->setTime(20, 30));

            if ($isStreakDay) {
                $this->streaks->recordAgendaView($user);
            }

            foreach ($questions->slice(($dayIndex * 4) % max(1, $questions->count()), $isStreakDay ? 4 : 2) as $answerIndex => $question) {
                Carbon::setTestNow(now()->addMinutes(3));

                /** ~3 em cada 4 respostas certas. */
                $isCorrect = $answerIndex % 4 !== 3;

                if ($question->type->usesOptions()) {
                    $this->answerQuestion->handle($user, $question, $question->options->firstWhere('is_correct', $isCorrect)?->id, null);
                } else {
                    $this->answerQuestion->handle($user, $question, null, $isCorrect ? $question->options->firstWhere('is_correct', true)?->content : '0');
                }
            }
        }
    }

    /**
     * Os STREAK_DAYS dias anteriores a hoje, do mais antigo ao mais recente.
     *
     * @return Collection<int, Carbon>
     */
    private function streakDays(): Collection
    {
        return collect(range(self::STREAK_DAYS, 1))->map(fn (int $daysAgo) => today()->subDays($daysAgo));
    }

    /**
     * @return array<int, Carbon>
     */
    private function touchedDays(): array
    {
        $days = [];

        for ($day = today()->subWeeks(self::WEEKS_OF_HISTORY); $day->lessThanOrEqualTo(today()); $day->addDay()) {
            $days[] = $day->copy();
        }

        return $days;
    }
}
