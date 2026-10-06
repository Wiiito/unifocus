<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;
use App\Models\DailyChallengeProgress;

/**
 * Desafios que, cumpridos no mesmo dia, mantêm o foguinho aceso.
 * Fonte única da lista: telas, cálculo e testes leem daqui.
 */
enum DailyChallenge: string
{
    use HasOptions;

    case AttendLesson = 'attend_lesson';
    case ViewAgenda = 'view_agenda';
    case AnswerQuestions = 'answer_questions';

    public function label(): string
    {
        return match ($this) {
            self::AttendLesson => __('Ir a uma aula'),
            self::ViewAgenda => __('Ver o calendário'),
            self::AnswerQuestions => __('Responder 3 questões'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::AttendLesson => __('Registre presença em pelo menos uma aula'),
            self::ViewAgenda => __('Abra sua agenda ao menos uma vez'),
            self::AnswerQuestions => __('Pratique com o banco de questões'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AttendLesson => 'school',
            self::ViewAgenda => 'calendar_month',
            self::AnswerQuestions => 'psychology',
        };
    }

    /**
     * Onde o estudante cumpre o desafio.
     */
    public function url(): string
    {
        return match ($this) {
            self::AttendLesson => route('enrollments.index'),
            self::ViewAgenda => route('agenda'),
            self::AnswerQuestions => route('practice.show'),
        };
    }

    /**
     * Quanto é preciso fazer no dia para cumprir o desafio.
     */
    public function target(): int
    {
        return match ($this) {
            self::AttendLesson, self::ViewAgenda => 1,
            self::AnswerQuestions => 3,
        };
    }

    /**
     * Quanto do desafio foi feito no dia registrado.
     */
    public function progressIn(?DailyChallengeProgress $progress): int
    {
        if ($progress === null) {
            return 0;
        }

        return match ($this) {
            self::AttendLesson => $progress->attended_lessons,
            self::ViewAgenda => $progress->viewed_agenda_at === null ? 0 : 1,
            self::AnswerQuestions => $progress->questions_answered,
        };
    }

    public function isMetBy(?DailyChallengeProgress $progress): bool
    {
        return $this->progressIn($progress) >= $this->target();
    }
}
