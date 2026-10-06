<?php

namespace App\Models;

use App\Enums\DailyChallenge;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Progresso de um estudante nos desafios de um dia. Escrito apenas pelo
 * StreakService.
 */
#[Table('daily_challenge_progress')]
#[Fillable(['user_id', 'day', 'attended_lessons', 'viewed_agenda_at', 'questions_answered', 'completed_at'])]
class DailyChallengeProgress extends Model
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'attended_lessons' => 0,
        'questions_answered' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day' => 'date',
            'attended_lessons' => 'integer',
            'viewed_agenda_at' => 'datetime',
            'questions_answered' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Todos os desafios do dia foram cumpridos.
     */
    public function meetsAllChallenges(): bool
    {
        return collect(DailyChallenge::cases())->every(fn (DailyChallenge $challenge) => $challenge->isMetBy($this));
    }
}
