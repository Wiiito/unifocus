<?php

namespace App\Models;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionSource;
use App\Enums\QuestionType;
use App\Enums\ReviewStatus;
use App\Models\Concerns\Auditable;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Questão reutilizável do banco. Hoje é cadastrada pelos admins; a geração
 * por IA vai gravar aqui também (source = ai, review_status = pending).
 */
#[Fillable(['subject_id', 'created_by_admin_id', 'ai_generation_id', 'statement', 'type', 'difficulty', 'topic', 'explanation', 'source', 'review_status'])]
class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use Auditable, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'difficulty' => QuestionDifficulty::class,
            'source' => QuestionSource::class,
            'review_status' => ReviewStatus::class,
            'times_used' => 'integer',
            'correct_rate' => 'float',
        ];
    }

    /**
     * Mantém o statement_hash sempre em sincronia com o enunciado: é ele que
     * impede a mesma questão de entrar duas vezes no banco.
     */
    protected function statement(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): array => [
                'statement' => $value,
                'statement_hash' => self::hashStatement($value),
            ],
        );
    }

    /**
     * Hash do enunciado normalizado (caixa e espaços não diferenciam questões).
     */
    public static function hashStatement(string $statement): string
    {
        return hash('sha256', Str::of($statement)->squish()->lower()->value());
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class)->withTrashed();
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    /**
     * @return BelongsTo<AiGeneration, $this>
     */
    public function aiGeneration(): BelongsTo
    {
        return $this->belongsTo(AiGeneration::class);
    }

    /**
     * @return HasMany<QuestionOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('position');
    }

    /**
     * @return HasOne<QuestionOption, $this>
     */
    public function correctOption(): HasOne
    {
        return $this->hasOne(QuestionOption::class)->where('is_correct', true);
    }

    /**
     * @return HasMany<QuestionAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuestionAttempt::class);
    }

    /**
     * Questões liberadas para os estudantes.
     */
    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('review_status', ReviewStatus::Approved);
    }
}
