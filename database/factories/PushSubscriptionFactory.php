<?php

namespace Database\Factories;

use App\Models\PushSubscription;
use App\Models\User;
use App\Support\WebPush\P256;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PushSubscription>
 */
class PushSubscriptionFactory extends Factory
{
    /**
     * Chaves de verdade (P-256 + segredo de 16 bytes): o payload pode ser
     * cifrado e decifrado nos testes como faria o navegador.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'endpoint' => 'https://push.example.test/send/'.fake()->unique()->uuid(),
            'public_key' => P256::base64UrlEncode(P256::generateKeyPair()['public']),
            'auth_token' => P256::base64UrlEncode(random_bytes(16)),
        ];
    }
}
