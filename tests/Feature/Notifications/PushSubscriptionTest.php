<?php

namespace Tests\Feature\Notifications;

use App\Http\Controllers\PushSubscriptionController;
use App\Models\PushSubscription;
use App\Models\User;
use App\Support\WebPush\P256;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const ENDPOINT = 'https://fcm.googleapis.com/fcm/send/abc123';

    public function test_guest_is_redirected_to_login(): void
    {
        $this->post(route('push-subscriptions.store'), $this->payload())->assertRedirect(route('login'));

        $this->assertSame(0, PushSubscription::count());
    }

    public function test_student_subscribes_this_browser(): void
    {
        $user = User::factory()->create();
        $payload = $this->payload();

        $this->actingAs($user)
            ->postJson(route('push-subscriptions.store'), $payload)
            ->assertNoContent()
            ->assertSessionHas(PushSubscriptionController::SESSION_KEY, self::ENDPOINT);

        $subscription = PushSubscription::sole();
        $this->assertSame($user->id, $subscription->user_id);
        $this->assertSame($payload['keys']['p256dh'], $subscription->public_key);
        $this->assertSame($payload['keys']['auth'], $subscription->auth_token);
    }

    public function test_browser_used_by_another_account_moves_to_the_logged_in_student(): void
    {
        PushSubscription::factory()->create(['endpoint' => self::ENDPOINT]);
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('push-subscriptions.store'), $this->payload())->assertNoContent();

        $this->assertSame($user->id, PushSubscription::sole()->user_id);
    }

    public function test_rejects_keys_of_the_wrong_size(): void
    {
        $payload = $this->payload();
        $payload['keys']['p256dh'] = P256::base64UrlEncode(random_bytes(32));

        $this->actingAs(User::factory()->create())
            ->postJson(route('push-subscriptions.store'), $payload)
            ->assertJsonValidationErrors(['keys.p256dh' => __('Chave de inscrição push inválida.')]);

        $this->assertSame(0, PushSubscription::count());
    }

    public function test_rejects_endpoint_without_https(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('push-subscriptions.store'), [...$this->payload(), 'endpoint' => 'http://push.example.test/abc'])
            ->assertJsonValidationErrors('endpoint');
    }

    public function test_student_unsubscribes_only_their_own_browser(): void
    {
        $user = User::factory()->create();
        $mine = PushSubscription::factory()->for($user)->create();
        $someoneElses = PushSubscription::factory()->create();

        $this->actingAs($user)
            ->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => $someoneElses->endpoint])
            ->assertNoContent();
        $this->actingAs($user)
            ->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => $mine->endpoint])
            ->assertNoContent();

        $this->assertModelExists($someoneElses);
        $this->assertModelMissing($mine);
    }

    public function test_logout_unsubscribes_the_browser_of_that_session(): void
    {
        $user = User::factory()->create();
        $otherDevice = PushSubscription::factory()->for($user)->create();
        $this->actingAs($user)->postJson(route('push-subscriptions.store'), $this->payload());

        $this->post(route('logout'))->assertRedirect('/');

        $this->assertSame([$otherDevice->id], PushSubscription::pluck('id')->all());
    }

    /**
     * @return array{endpoint: string, keys: array{p256dh: string, auth: string}}
     */
    private function payload(): array
    {
        return [
            'endpoint' => self::ENDPOINT,
            'keys' => [
                'p256dh' => P256::base64UrlEncode(P256::generateKeyPair()['public']),
                'auth' => P256::base64UrlEncode(random_bytes(16)),
            ],
        ];
    }
}
