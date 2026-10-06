<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\AcademicTermFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Período letivo de uma instituição (2026.1, 2026.2...). Cada instituição tem
 * o próprio calendário, com seus marcos (semana de provas, recesso...).
 */
#[Fillable(['institution_id', 'created_by_admin_id', 'name', 'starts_on', 'ends_on'])]
class AcademicTerm extends Model
{
    /** @use HasFactory<AcademicTermFactory> */
    use Auditable, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Institution, $this>
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * @return HasMany<AcademicTermEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(AcademicTermEvent::class)->orderBy('starts_on');
    }

    /**
     * @return HasMany<ClassGroup, $this>
     */
    public function classGroups(): HasMany
    {
        return $this->hasMany(ClassGroup::class);
    }

    /**
     * Períodos em andamento hoje.
     */
    #[Scope]
    protected function current(Builder $query): void
    {
        $today = today()->toDateString();

        $query->whereDate('starts_on', '<=', $today)->whereDate('ends_on', '>=', $today);
    }

    public function isCurrent(): bool
    {
        return today()->betweenIncluded($this->starts_on, $this->ends_on);
    }
}
