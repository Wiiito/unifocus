<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Registra em audit_logs quem criou, alterou ou removeu o registro, com os
 * valores antes e depois.
 *
 * @mixin Model
 */
trait Auditable
{
    /**
     * Colunas que mudam sozinhas e só gerariam ruído no histórico.
     *
     * @var array<int, string>
     */
    protected static array $auditIgnored = ['created_at', 'updated_at', 'deleted_at'];

    public static function bootAuditable(): void
    {
        static::created(fn (Model $model) => AuditLog::record($model, 'created', [], $model->auditableAttributes($model->getAttributes())));

        static::updated(function (Model $model): void {
            $changes = $model->auditableAttributes($model->getChanges());

            if ($changes === []) {
                return;
            }

            AuditLog::record($model, 'updated', array_intersect_key($model->getOriginal(), $changes), $changes);
        });

        static::deleted(fn (Model $model) => AuditLog::record($model, 'deleted', $model->auditableAttributes($model->getAttributes()), []));
    }

    /**
     * @return MorphMany<AuditLog, $this>
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function auditableAttributes(array $attributes): array
    {
        return array_diff_key($attributes, array_flip(static::$auditIgnored));
    }
}
