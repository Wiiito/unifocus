<?php

namespace App\Models;

use App\Contracts\AffectsDailyChallenges;
use App\Observers\DailyChallengeObserver;
use Carbon\CarbonInterface;
use Database\Factories\QuestionAttemptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Resposta de um estudante a uma questão.
 */
#[Fillable(['user_id', 'question_id', 'question_option_id', 'answer_text', 'is_correct', 'time_spent_seconds', 'answered_at'])]
#[ObservedBy(DailyChallengeObserver::class)]
class QuestionAttempt extends Model implements AffectsDailyChallenges
{
    /** @use HasFactory<QuestionAttemptFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'time_spent_seconds' => 'integer',
            'answered_at' => 'datetime',
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
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * @return BelongsTo<QuestionOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }

    /**
     * @return iterable<int, array{user: User, day: CarbonInterface}>
     */
    public function dailyChallengeDays(): iterable
    {
        return [['user' => $this->user, 'day' => $this->answered_at ?? now()]];
    }
}
