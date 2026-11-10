<?php

namespace App\Support\WebPush;

use App\Models\PushSubscription;
use App\Notifications\Messages\WebPushMessage;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Entrega uma mensagem a um navegador inscrito: cifra o conteúdo com as
 * chaves dele, assina com VAPID e faz o POST no serviço de push. Inscrições
 * que o serviço diz não existirem mais (404/410) são apagadas aqui.
 */
class WebPushSender
{
    public function isConfigured(): bool
    {
        return VapidKeys::fromConfig() !== null;
    }

    public function send(PushSubscription $subscription, WebPushMessage $message): Response
    {
        $vapid = VapidKeys::fromConfig() ?? throw new RuntimeException('Web Push sem chaves VAPID (php artisan webpush:vapid).');

        $body = PayloadEncrypter::encrypt(
            json_encode($message->toPayload(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            P256::base64UrlDecode($subscription->public_key),
            P256::base64UrlDecode($subscription->auth_token),
        );

        $response = Http::withHeaders(array_filter([
            'Authorization' => $vapid->authorizationHeader($subscription->endpoint),
            'Content-Encoding' => 'aes128gcm',
            'TTL' => (string) config('webpush.ttl'),
            'Urgency' => $message->urgency,
            'Topic' => $message->topic(),
        ]))
            ->timeout(10)
            ->withBody($body, 'application/octet-stream')
            ->post($subscription->endpoint);

        if (in_array($response->status(), [404, 410], true)) {
            $subscription->delete();
        }

        return $response;
    }
}
