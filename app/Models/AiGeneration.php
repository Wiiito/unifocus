<?php

namespace App\Models;

use App\Enums\AiGenerationPurpose;
use App\Enums\AiGenerationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Registro de uma chamada a um provedor de IA (custo, tokens, latência).
 * Ainda não é escrito por nenhum fluxo: existe para que o futuro
 * QuestionGenerator tenha onde registrar cada geração.
 */
#[Fillable(['subject_id', 'purpose', 'provider', 'model', 'prompt', 'prompt_hash', 'tokens_input', 'tokens_output', 'cost_cents', 'latency_ms', 'status', 'error'])]
class AiGeneration extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purpose' => AiGenerationPurpose::class,
            'status' => AiGenerationStatus::class,
            'tokens_input' => 'integer',
            'tokens_output' => 'integer',
            'cost_cents' => 'integer',
            'latency_ms' => 'integer',
        ];
    }

    /**
     * Estudante ou admin que disparou a geração.
     *
     * @return MorphTo<Model, $this>
     */
    public function requestedBy(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
