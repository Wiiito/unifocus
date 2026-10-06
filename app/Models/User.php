<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\MembershipStatus;
use App\Models\Concerns\HasOauthIdentities;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasOauthIdentities, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasMany<InstitutionMember, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(InstitutionMember::class);
    }

    /**
     * @return BelongsToMany<Institution, $this>
     */
    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class, 'institution_members')
            ->using(InstitutionMember::class)
            ->withPivot(['id', 'role', 'status', 'registration_code', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * IDs das instituições com vínculo ativo, memorizados por instância: várias
     * consultas da mesma requisição (catálogo, períodos) dependem deles.
     *
     * @return array<int, int>
     */
    public function activeInstitutionIds(): array
    {
        return once(fn (): array => $this->memberships()
            ->where('status', MembershipStatus::Active)
            ->pluck('institution_id')
            ->all());
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return HasMany<QuestionAttempt, $this>
     */
    public function questionAttempts(): HasMany
    {
        return $this->hasMany(QuestionAttempt::class);
    }
}
