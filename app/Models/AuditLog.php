<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

/**
 * Trilha de auditoria. Só tem created_at: um registro de auditoria nunca é
 * alterado depois de escrito.
 */
#[Fillable(['action', 'old_values', 'new_values', 'ip_address'])]
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    /**
     * Grava uma entrada para o model informado. O autor é quem está
     * autenticado no guard ativo da requisição (o painel admin troca o guard
     * padrão para "admin" no middleware), ou ninguém, em jobs e comandos.
     *
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public static function record(Model $auditable, string $action, array $oldValues, array $newValues): self
    {
        $log = new self([
            'action' => $action,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()?->ip(),
        ]);

        $log->auditable()->associate($auditable);
        $log->actor()->associate(Auth::user());
        $log->save();

        return $log;
    }

    /**
     * Frase curta para listagens: "Ana alterou Nota #12".
     */
    public function summary(): string
    {
        $actor = $this->actor?->name ?? __('Sistema');

        $action = match ($this->action) {
            'created' => __('criou'),
            'updated' => __('alterou'),
            'deleted' => __('removeu'),
            default => $this->action,
        };

        $subject = match ($this->auditable_type) {
            (new GradeEntry)->getMorphClass() => __('Nota'),
            (new LessonAttendance)->getMorphClass() => __('Presença'),
            (new Subject)->getMorphClass() => __('Matéria'),
            (new Institution)->getMorphClass() => __('Instituição'),
            (new AcademicTerm)->getMorphClass() => __('Período'),
            (new Question)->getMorphClass() => __('Questão'),
            default => class_basename($this->auditable_type),
        };

        return "{$actor} {$action} {$subject} #{$this->auditable_id}";
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Quem fez a alteração: um User ou um Admin.
     *
     * @return MorphTo<Model, $this>
     */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}
