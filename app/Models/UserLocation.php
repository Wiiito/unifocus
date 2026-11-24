<?php

namespace App\Models;

use Database\Factories\UserLocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Posição capturada pelo GPS do navegador (resources/js/location-tracker.js)
 * e gravada pelo UserLocationController. Só registra: nada aqui é exibido
 * para outros estudantes.
 */
#[Fillable(['user_id', 'latitude', 'longitude', 'accuracy', 'source', 'user_agent', 'recorded_at'])]
#[Hidden(['user_agent'])]
class UserLocation extends Model
{
    /** @use HasFactory<UserLocationFactory> */
    use HasFactory;

    public const DEFAULT_SOURCE = 'browser_gps';

    /** A tabela só tem recorded_at: a posição nunca é editada depois de gravada. */
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'accuracy' => 'float',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
