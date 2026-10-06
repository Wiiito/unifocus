<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Foguinho de um estudante. Escrito apenas pelo StreakService; qualquer tela
 * (inclusive a de outros usuários) lê por aqui.
 */
#[Fillable(['user_id', 'current_count', 'longest_count', 'last_completed_on'])]
class UserStreak extends Model
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'current_count' => 0,
        'longest_count' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_count' => 'integer',
            'longest_count' => 'integer',
            'last_completed_on' => 'date',
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
     * Ainda dá para manter a sequência: o último dia cumprido foi hoje ou
     * ontem. Antes disso o foguinho apagou, sem precisar de rotina agendada.
     */
    public function isAlive(): bool
    {
        return $this->last_completed_on !== null && $this->last_completed_on->greaterThanOrEqualTo(today()->subDay());
    }

    /**
     * Valor exibido do foguinho.
     */
    public function activeCount(): int
    {
        return $this->isAlive() ? $this->current_count : 0;
    }

    public function isCompletedToday(): bool
    {
        return $this->last_completed_on?->isToday() ?? false;
    }
}
