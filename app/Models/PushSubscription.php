<?php

namespace App\Models;

use Database\Factories\PushSubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Inscrição Web Push de um navegador. Criada/atualizada pelo
 * PushSubscriptionController e apagada quando o serviço de push avisa que
 * ela expirou (404/410) ou quando a pessoa sai da conta naquele navegador.
 */
#[Fillable(['user_id', 'endpoint', 'public_key', 'auth_token'])]
#[Hidden(['public_key', 'auth_token'])]
class PushSubscription extends Model
{
    /** @use HasFactory<PushSubscriptionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
