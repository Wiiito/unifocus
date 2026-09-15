<?php

namespace App\Models\Concerns;

use App\Models\OauthIdentity;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Habilita qualquer model autenticável a fazer login via provedores OAuth.
 */
trait HasOauthIdentities
{
    /**
     * Identidades OAuth vinculadas a este registro.
     *
     * @return MorphMany<OauthIdentity, $this>
     */
    public function oauthIdentities(): MorphMany
    {
        return $this->morphMany(OauthIdentity::class, 'authenticatable');
    }

    /**
     * Identidade vinculada a um provedor específico, quando existir.
     */
    public function oauthIdentityFor(string $provider): ?OauthIdentity
    {
        return $this->oauthIdentities()->firstWhere('provider', $provider);
    }

    /**
     * Indica se este registro já está vinculado ao provedor informado.
     */
    public function hasOauthIdentityFor(string $provider): bool
    {
        return $this->oauthIdentities()->where('provider', $provider)->exists();
    }
}
