<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\InstitutionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Instituição de ensino, cadastrada pelos admins. Define as regras acadêmicas
 * (pontuação e limite de faltas) herdadas pelas matrículas.
 */
#[Fillable(['name', 'slug', 'document', 'total_points', 'passing_percent', 'max_absence_percent'])]
class Institution extends Model
{
    /**
     * Valores sugeridos ao cadastrar uma instituição (cada uma ajusta os
     * seus). Espelham os defaults das colunas na migration.
     */
    public const DEFAULT_TOTAL_POINTS = 100;

    public const DEFAULT_PASSING_PERCENT = 60.0;

    public const DEFAULT_MAX_ABSENCE_PERCENT = 25;

    /** @use HasFactory<InstitutionFactory> */
    use Auditable, HasFactory, SoftDeletes;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'total_points' => self::DEFAULT_TOTAL_POINTS,
        'passing_percent' => self::DEFAULT_PASSING_PERCENT,
        'max_absence_percent' => self::DEFAULT_MAX_ABSENCE_PERCENT,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_points' => 'integer',
            'passing_percent' => 'float',
            'max_absence_percent' => 'integer',
        ];
    }

    /**
     * @return HasMany<InstitutionMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(InstitutionMember::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'institution_members')
            ->using(InstitutionMember::class)
            ->withPivot(['role', 'status', 'registration_code', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<AcademicTerm, $this>
     */
    public function academicTerms(): HasMany
    {
        return $this->hasMany(AcademicTerm::class);
    }

    /**
     * @return HasMany<Subject, $this>
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }
}
