<?php

namespace Tests\Feature\Notifications;

use App\Models\Friendship;
use App\Models\PushSubscription;
use App\Models\User;
use App\Support\WebPush\P256;
use App\Support\WebPush\VapidKeys;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\DecryptsWebPush;
use Tests\TestCase;

class WebPushDeliveryTest extends TestCase
{
    use DecryptsWebPush, LazilyRefreshDatabase;

    private const ENDPOINT = 'https://push.example.test/send/abc';

    public function test_chat_message_is_pushed_encrypted_and_signed_to_the_recipient(): void
    {
        $vapid = $this->configureVapid();
        [$recipient, $browserKeys, $authSecret] = $this->subscribedUser();
        $sender = User::factory()->create(['name' => 'Ana Souza']);
        Friendship::factory()->accepted()->create(['requester_id' => $sender->id, 'addressee_id' => $recipient->id]);
        Http::preventStrayRequests();
        Http::fake([self::ENDPOINT => Http::response(status: 201)]);

        $this->actingAs($sender)
            ->postJson(route('messages.store', $recipient->id), ['body' => 'Bora estudar?'])
            ->assertCreated();

        Http::assertSent(function (Request $request) use ($vapid, $browserKeys, $authSecret, $sender): bool {
            $payload = json_decode($this->decryptWebPush($request->body(), $browserKeys, $authSecret), true);

            $this->assertSame('aes128gcm', $request->header('Content-Encoding')[0]);
            $this->assertSame('high', $request->header('Urgency')[0]);
            $this->assertSame("message-{$sender->id}", $request->header('Topic')[0]);
            $this->assertValidVapidSignature($request->header('Authorization')[0], $vapid);
            $this->assertSame('Ana Souza', $payload['title']);
            $this->assertSame('Bora estudar?', $payload['body']);
            $this->assertSame(route('messages.show', $sender->id), $payload['url']);

            return $request->url() === self::ENDPOINT;
        });
    }

    public function test_push_of_a_bell_notification_opens_it_through_the_mark_as_read_route(): void
    {
        $this->configureVapid();
        [$recipient, $browserKeys, $authSecret] = $this->subscribedUser();
        Http::preventStrayRequests();
        Http::fake([self::ENDPOINT => Http::response(status: 201)]);

        $this->actingAs(User::factory()->create())->post(route('friends.store'), ['email' => $recipient->email]);

        $notification = $recipient->notifications()->sole();
        Http::assertSent(function (Request $request) use ($browserKeys, $authSecret, $notification): bool {
            $payload = json_decode($this->decryptWebPush($request->body(), $browserKeys, $authSecret), true);

            return $payload['url'] === route('notifications.show', $notification->id);
        });
    }

    public function test_subscription_is_removed_when_the_push_service_says_it_expired(): void
    {
        $this->configureVapid();
        [$recipient] = $this->subscribedUser();
        $subscription = $recipient->pushSubscriptions()->sole();
        Http::preventStrayRequests();
        Http::fake([self::ENDPOINT => Http::response(status: 410)]);

        $this->actingAs(User::factory()->create())->post(route('friends.store'), ['email' => $recipient->email]);

        $this->assertModelMissing($subscription);
    }

    public function test_without_vapid_keys_nothing_is_pushed_but_the_bell_still_gets_the_notification(): void
    {
        config(['webpush.vapid.public_key' => null, 'webpush.vapid.private_key' => null]);
        [$recipient] = $this->subscribedUser();
        Http::preventStrayRequests();

        $this->actingAs(User::factory()->create())->post(route('friends.store'), ['email' => $recipient->email]);

        Http::assertNothingSent();
        $this->assertSame(1, $recipient->notifications()->count());
    }

    private function configureVapid(): VapidKeys
    {
        $vapid = VapidKeys::generate('mailto:suporte@unifocus.test');

        config([
            'webpush.vapid.public_key' => $vapid->encodedPublicKey(),
            'webpush.vapid.private_key' => $vapid->encodedPrivateKey(),
            'webpush.vapid.subject' => 'mailto:suporte@unifocus.test',
        ]);

        return $vapid;
    }

    /**
     * @return array{0: User, 1: array{public: string, private: string}, 2: string}
     */
    private function subscribedUser(): array
    {
        $browserKeys = P256::generateKeyPair();
        $authSecret = random_bytes(16);
        $subscription = PushSubscription::factory()->create([
            'endpoint' => self::ENDPOINT,
            'public_key' => P256::base64UrlEncode($browserKeys['public']),
            'auth_token' => P256::base64UrlEncode($authSecret),
        ]);

        return [$subscription->user, $browserKeys, $authSecret];
    }

    private function assertValidVapidSignature(string $authorization, VapidKeys $vapid): void
    {
        preg_match('/^vapid t=([^,]+), k=(.+)$/', $authorization, $matches);
        [$header, $claims, $signature] = explode('.', $matches[1]);
        $raw = P256::base64UrlDecode($signature);

        $this->assertSame($vapid->encodedPublicKey(), $matches[2]);
        $this->assertSame('https://push.example.test', json_decode(P256::base64UrlDecode($claims), true)['aud']);
        $this->assertSame(1, openssl_verify(
            "{$header}.{$claims}",
            $this->rawSignatureToDer(substr($raw, 0, 32), substr($raw, 32)),
            P256::publicKeyFromRaw($vapid->publicKey),
            OPENSSL_ALGO_SHA256,
        ));
    }

    private function rawSignatureToDer(string $r, string $s): string
    {
        $integer = function (string $value): string {
            $value = ltrim($value, "\x00");
            $value = ord($value[0]) > 0x7F ? "\x00".$value : $value;

            return "\x02".chr(strlen($value)).$value;
        };

        $sequence = $integer($r).$integer($s);

        return "\x30".chr(strlen($sequence)).$sequence;
    }
}
