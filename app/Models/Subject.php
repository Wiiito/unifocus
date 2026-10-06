<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Matéria do catálogo (a DEFINIÇÃO). Não confundir com a turma, que é a
 * matéria cursada em um período.
 */
#[Fillable(['institution_id', 'name', 'code', 'description', 'credits', 'workload_hours', 'color', 'created_by_admin_id'])]
class Subject extends Model
{
    /** Cor usada quando a matéria não tem uma definida. */
    public const DEFAULT_COLOR = '#5c7cfa';

    /** @use HasFactory<SubjectFactory> */
    use Auditable, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'workload_hours' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    /**
     * Instituição dona da matéria; NULL = catálogo geral.
     *
     * @return BelongsTo<Institution, $this>
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * @return HasMany<ClassGroup, $this>
     */
    public function classGroups(): HasMany
    {
        return $this->hasMany(ClassGroup::class);
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Matérias que o estudante pode cursar: as do catálogo geral e as das
     * instituições às quais ele está vinculado.
     */
    #[Scope]
    protected function availableTo(Builder $query, User $user): void
    {
        $query->where(function (Builder $query) use ($user): void {
            $query->whereNull('institution_id')
                ->orWhereIn('institution_id', $user->activeInstitutionIds());
        });
    }

    public function displayColor(): string
    {
        return $this->color ?? self::DEFAULT_COLOR;
    }
}
