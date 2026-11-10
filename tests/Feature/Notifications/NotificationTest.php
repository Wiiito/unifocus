<?php

namespace Tests\Feature\Notifications;

use App\Models\Friendship;
use App\Models\User;
use App\Notifications\FriendRequestAccepted;
use App\Notifications\FriendRequestReceived;
use App\Notifications\NewDirectMessage;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_friend_request_notifies_the_addressee(): void
    {
        $requester = User::factory()->create();
        $addressee = User::factory()->create();
        Notification::fake();

        $this->actingAs($requester)->post(route('friends.store'), ['email' => $addressee->email]);

        Notification::assertSentTo($addressee, FriendRequestReceived::class, fn (FriendRequestReceived $notification) => $notification->requester->is($requester));
    }

    public function test_mutual_request_tells_the_first_requester_it_was_accepted_instead_of_a_new_request(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        Friendship::factory()->create(['requester_id' => $first->id, 'addressee_id' => $second->id]);
        Notification::fake();

        $this->actingAs($second)->post(route('friends.store'), ['email' => $first->email]);

        Notification::assertSentTo($first, FriendRequestAccepted::class, fn (FriendRequestAccepted $notification) => $notification->friend->is($second));
        Notification::assertNotSentTo($first, FriendRequestReceived::class);
    }

    public function test_accepting_a_request_notifies_the_requester(): void
    {
        $requester = User::factory()->create();
        $addressee = User::factory()->create();
        $friendship = Friendship::factory()->create(['requester_id' => $requester->id, 'addressee_id' => $addressee->id]);
        Notification::fake();

        $this->actingAs($addressee)->patch(route('friends.update', $friendship));

        Notification::assertSentTo($requester, FriendRequestAccepted::class, fn (FriendRequestAccepted $notification) => $notification->friend->is($addressee));
    }

    public function test_chat_message_notifies_the_recipient_only_by_push(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        Friendship::factory()->accepted()->create(['requester_id' => $sender->id, 'addressee_id' => $recipient->id]);

        $this->actingAs($sender)->postJson(route('messages.store', $recipient->id), ['body' => 'Oi!'])->assertCreated();

        $this->assertSame(0, $recipient->notifications()->count());

        Notification::fake();
        $this->actingAs($sender)->postJson(route('messages.store', $recipient->id), ['body' => 'Tudo bem?']);
        Notification::assertSentTo($recipient, NewDirectMessage::class, fn (NewDirectMessage $notification) => $notification->chatMessage->body === 'Tudo bem?');
    }

    public function test_bell_lists_notifications_with_unread_count(): void
    {
        $user = User::factory()->create();
        $user->notify(new FriendRequestReceived(User::factory()->create(['name' => 'Bruno Lima'])));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Notificações, 1 não lida')
            ->assertSee('Bruno Lima quer ser seu amigo no UniFocus.');
    }

    public function test_opening_a_notification_marks_it_read_and_redirects_to_its_destination(): void
    {
        $user = User::factory()->create();
        $user->notify(new FriendRequestReceived(User::factory()->create()));
        $notification = $user->notifications()->sole();

        $this->actingAs($user)
            ->get(route('notifications.show', $notification->id))
            ->assertRedirect(route('friends.index'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_cannot_open_another_students_notification(): void
    {
        $owner = User::factory()->create();
        $owner->notify(new FriendRequestReceived(User::factory()->create()));
        $notification = $owner->notifications()->sole();

        $this->actingAs(User::factory()->create())
            ->get(route('notifications.show', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read_only_touches_own_notifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $user->notify(new FriendRequestReceived($other));
        $other->notify(new FriendRequestReceived($user));

        $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('notifications.read-all'))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(0, $user->unreadNotifications()->count());
        $this->assertSame(1, $other->unreadNotifications()->count());
    }
}
