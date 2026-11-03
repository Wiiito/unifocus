<?php

namespace App\Models;

use App\Enums\FriendshipStatus;
use Database\Factories\FriendshipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pedido de amizade (pending) ou amizade (accepted) entre dois estudantes.
 * Escrito apenas pelas actions de App\Actions\Friendships.
 */
#[Fillable(['requester_id', 'addressee_id', 'status', 'accepted_at'])]
class Friendship extends Model
{
    /** @use HasFactory<FriendshipFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => FriendshipStatus::class,
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressee_id');
    }

    #[Scope]
    protected function accepted(Builder $query): void
    {
        $query->where('status', FriendshipStatus::Accepted);
    }

    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', FriendshipStatus::Pending);
    }

    /**
     * Vínculos em que a pessoa participa, em qualquer direção.
     */
    #[Scope]
    protected function involving(Builder $query, User $user): void
    {
        $query->where(fn (Builder $inner) => $inner
            ->where('requester_id', $user->id)
            ->orWhere('addressee_id', $user->id));
    }

    /**
     * O vínculo entre duas pessoas, seja qual for quem pediu.
     */
    #[Scope]
    protected function between(Builder $query, User $first, User $second): void
    {
        $query->where(fn (Builder $inner) => $inner
            ->where(fn (Builder $pair) => $pair->where('requester_id', $first->id)->where('addressee_id', $second->id))
            ->orWhere(fn (Builder $pair) => $pair->where('requester_id', $second->id)->where('addressee_id', $first->id)));
    }

    public function isAccepted(): bool
    {
        return $this->status === FriendshipStatus::Accepted;
    }

    /**
     * Quem está do outro lado do vínculo. Precisa das duas relações carregadas
     * (with(['requester', 'addressee'])) ao listar vários.
     */
    public function otherParty(User $me): User
    {
        return (int) $this->requester_id === (int) $me->id ? $this->addressee : $this->requester;
    }
}
