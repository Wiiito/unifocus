<?php

namespace Tests\Feature\Friends;

use App\Enums\FriendshipStatus;
use App\Models\Friendship;
use App\Models\User;
use App\Models\UserStreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FriendshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sends_a_friend_request_by_email(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)
            ->post(route('friends.store'), ['email' => $other->email])
            ->assertRedirect(route('friends.index'));

        $friendship = Friendship::sole();
        $this->assertSame($user->id, $friendship->requester_id);
        $this->assertSame($other->id, $friendship->addressee_id);
        $this->assertSame(FriendshipStatus::Pending, $friendship->status);
    }

    public function test_rejects_unknown_email_self_and_duplicates(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Friendship::factory()->create(['requester_id' => $user->id, 'addressee_id' => $other->id]);

        $this->actingAs($user)->post(route('friends.store'), ['email' => 'ninguem@example.com'])
            ->assertSessionHasErrors(['email' => __('Não encontramos ninguém com este e-mail.')]);

        $this->actingAs($user)->post(route('friends.store'), ['email' => $user->email])
            ->assertSessionHasErrors(['email' => __('Você não pode adicionar a si mesmo.')]);

        $this->actingAs($user)->post(route('friends.store'), ['email' => $other->email])
            ->assertSessionHasErrors(['email' => __('Você já enviou um pedido para esta pessoa.')]);

        $this->assertSame(1, Friendship::count());
    }

    public function test_requesting_someone_who_already_requested_you_makes_you_friends(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Friendship::factory()->create(['requester_id' => $other->id, 'addressee_id' => $user->id]);

        $this->actingAs($user)->post(route('friends.store'), ['email' => $other->email]);

        $this->assertSame(FriendshipStatus::Accepted, Friendship::sole()->status);
        $this->assertTrue($user->friends()->whereKey($other->id)->exists());
        $this->assertTrue($other->friends()->whereKey($user->id)->exists());
    }

    public function test_only_the_addressee_can_accept_a_request(): void
    {
        $requester = User::factory()->create();
        $addressee = User::factory()->create();
        $friendship = Friendship::factory()->create(['requester_id' => $requester->id, 'addressee_id' => $addressee->id]);

        $this->actingAs($requester)->patch(route('friends.update', $friendship))->assertNotFound();
        $this->actingAs(User::factory()->create())->patch(route('friends.update', $friendship))->assertNotFound();
        $this->assertSame(FriendshipStatus::Pending, $friendship->fresh()->status);

        $this->actingAs($addressee)->patch(route('friends.update', $friendship))->assertRedirect(route('friends.index'));

        $friendship->refresh();
        $this->assertSame(FriendshipStatus::Accepted, $friendship->status);
        $this->assertNotNull($friendship->accepted_at);
    }

    public function test_either_party_can_remove_but_strangers_cannot(): void
    {
        $friendship = Friendship::factory()->accepted()->create();

        $this->actingAs(User::factory()->create())->delete(route('friends.destroy', $friendship))->assertNotFound();
        $this->assertModelExists($friendship);

        $this->actingAs($friendship->addressee)->delete(route('friends.destroy', $friendship))
            ->assertRedirect(route('friends.index'));
        $this->assertModelMissing($friendship);
    }

    public function test_friends_page_lists_friends_with_their_streak_and_pending_requests(): void
    {
        $user = User::factory()->create();
        $friend = User::factory()->create(['name' => 'Mariana Costa']);
        $requester = User::factory()->create(['name' => 'Lucas Silva']);
        UserStreak::create(['user_id' => $friend->id, 'current_count' => 5, 'longest_count' => 5, 'last_completed_on' => today()]);
        Friendship::factory()->accepted()->create(['requester_id' => $friend->id, 'addressee_id' => $user->id]);
        Friendship::factory()->create(['requester_id' => $requester->id, 'addressee_id' => $user->id]);

        $this->actingAs($user)->get(route('friends.index'))
            ->assertOk()
            ->assertSeeInOrder(['Pedidos recebidos', 'Lucas Silva', 'Seus amigos', 'Mariana Costa'])
            ->assertSee('5 dias de foguinho');
    }

    public function test_guests_cannot_see_friends(): void
    {
        $this->get(route('friends.index'))->assertRedirect(route('login'));
    }
}
