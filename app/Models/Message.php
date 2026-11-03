<?php

namespace App\Models;

use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mensagem direta entre amigos. Escrita apenas pela action SendMessage.
 */
#[Fillable(['sender_id', 'recipient_id', 'body', 'read_at'])]
class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * A conversa entre duas pessoas, nos dois sentidos.
     */
    #[Scope]
    protected function conversation(Builder $query, User $first, User $second): void
    {
        $query->where(fn (Builder $inner) => $inner
            ->where(fn (Builder $pair) => $pair->where('sender_id', $first->id)->where('recipient_id', $second->id))
            ->orWhere(fn (Builder $pair) => $pair->where('sender_id', $second->id)->where('recipient_id', $first->id)));
    }

    #[Scope]
    protected function unread(Builder $query): void
    {
        $query->whereNull('read_at');
    }
}
