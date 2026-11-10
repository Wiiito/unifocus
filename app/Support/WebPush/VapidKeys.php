<?php

namespace App\Support\WebPush;

/**
 * Identidade VAPID do servidor (RFC 8292): o par de chaves que assina um JWT
 * em cada envio, provando ao serviço de push que somos o mesmo servidor que
 * o navegador autorizou ao se inscrever (applicationServerKey).
 */
final readonly class VapidKeys
{
    /** Validade do JWT; o máximo aceito pelos serviços de push é 24h. */
    private const TOKEN_TTL_SECONDS = 12 * 3600;

    /**
     * @param  string  $publicKey  Ponto público cru (65 bytes).
     * @param  string  $privateKey  Escalar privado cru (32 bytes).
     */
    public function __construct(
        public string $publicKey,
        public string $privateKey,
        public string $subject,
    ) {}

    public static function generate(string $subject): self
    {
        $pair = P256::generateKeyPair();

        return new self($pair['public'], $pair['private'], $subject);
    }

    /**
     * Chaves de config/webpush.php, ou null quando o push não foi configurado.
     */
    public static function fromConfig(): ?self
    {
        $public = config('webpush.vapid.public_key');
        $private = config('webpush.vapid.private_key');

        if (blank($public) || blank($private)) {
            return null;
        }

        return new self(P256::base64UrlDecode($public), P256::base64UrlDecode($private), config('webpush.vapid.subject'));
    }

    public function encodedPublicKey(): string
    {
        return P256::base64UrlEncode($this->publicKey);
    }

    public function encodedPrivateKey(): string
    {
        return P256::base64UrlEncode($this->privateKey);
    }

    /**
     * Valor do cabeçalho Authorization para um endpoint de push. O "aud" do
     * JWT é a origem do serviço de push (ex.: https://fcm.googleapis.com).
     */
    public function authorizationHeader(string $endpoint): string
    {
        $audience = parse_url($endpoint, PHP_URL_SCHEME).'://'.parse_url($endpoint, PHP_URL_HOST);

        $signingInput = P256::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'ES256']))
            .'.'.P256::base64UrlEncode(json_encode([
                'aud' => $audience,
                'exp' => now()->addSeconds(self::TOKEN_TTL_SECONDS)->getTimestamp(),
                'sub' => $this->subject,
            ]));

        openssl_sign($signingInput, $signature, P256::privateKeyFromRaw($this->privateKey, $this->publicKey), OPENSSL_ALGO_SHA256);

        $token = $signingInput.'.'.P256::base64UrlEncode(P256::derSignatureToRaw($signature));

        return "vapid t={$token}, k={$this->encodedPublicKey()}";
    }
}
