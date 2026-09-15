<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Vínculo entre uma conta de um provedor OAuth e um model autenticável da
 * aplicação. A relação é polimórfica de propósito: o mesmo provedor pode
 * autenticar entidades diferentes (User, Operador, Cliente, ...).
 */
#[Fillable(['provider', 'provider_id', 'name', 'nickname', 'email', 'avatar', 'token', 'refresh_token', 'token_expires_at'])]
#[Hidden(['token', 'refresh_token'])]
class OauthIdentity extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
        ];
    }

    /**
     * Entidade da aplicação dona desta identidade.
     *
     * @return MorphTo<Model, $this>
     */
    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Provedores declarados em config/oauth.php, já mesclados com os padrões.
     *
     * @return array<string, array{label: string, scopes: array<int, string>, with: array<string, string>, model: class-string, guard: string, redirect: string}>
     */
    public static function providers(): array
    {
        $defaults = config('oauth.defaults');

        return collect(config('oauth.providers'))
            ->map(fn (array $provider, string $name): array => array_merge([
                'label' => str($name)->headline()->value(),
                'scopes' => [],
                'with' => [],
            ], $defaults, $provider))
            ->all();
    }

    /**
     * Provedores que, além de declarados, já possuem credenciais preenchidas.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function configuredProviders(): array
    {
        return collect(static::providers())
            ->filter(fn (array $provider, string $name): bool => filled(config("services.{$name}.client_id")))
            ->all();
    }
}
