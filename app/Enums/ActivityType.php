<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Tipo de atividade/avaliação.
 */
enum ActivityType: string
{
    use HasOptions;

    case Homework = 'homework';
    case Exam = 'exam';
    case Recovery = 'recovery';
    case Project = 'project';
    case Quiz = 'quiz';
    case Reading = 'reading';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Homework => __('Tarefa'),
            self::Exam => __('Prova'),
            self::Recovery => __('Recuperação'),
            self::Project => __('Trabalho'),
            self::Quiz => __('Quiz'),
            self::Reading => __('Leitura'),
            self::Other => __('Outra'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Homework => 'assignment',
            self::Exam => 'quiz',
            self::Recovery => 'restart_alt',
            self::Project => 'groups',
            self::Quiz => 'psychology_alt',
            self::Reading => 'menu_book',
            self::Other => 'task_alt',
        };
    }
}
