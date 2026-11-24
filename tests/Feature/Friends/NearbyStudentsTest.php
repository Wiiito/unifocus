<?php

namespace Tests\Feature\Friends;

use App\Enums\FriendshipStatus;
use App\Models\Friendship;
use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NearbyStudentsTest extends TestCase
{
    use RefreshDatabase;

    /** Praça da Liberdade, Belo Horizonte. */
    private const LATITUDE = -19.9320;

    private const LONGITUDE = -43.9380;

    public function test_lists_students_whose_latest_recent_position_is_within_the_radius(): void
    {
        $user = $this->userAt(self::LATITUDE, self::LONGITUDE);
        $this->userAt(self::LATITUDE + 0.018, self::LONGITUDE, ['name' => 'Ana Perto']);
        $this->userAt(self::LATITUDE + 0.002, self::LONGITUDE, ['name' => 'Bruno Vizinho']);
        $this->userAt(self::LATITUDE + 0.2, self::LONGITUDE, ['name' => 'Carla Longe']);
        $this->userAt(self::LATITUDE, self::LONGITUDE, ['name' => 'Davi Antigo'], now()->subDays(8));

        $movedAway = $this->userAt(self::LATITUDE, self::LONGITUDE, ['name' => 'Eva Mudou'], now()->subDay());
        UserLocation::factory()->for($movedAway)->create(['latitude' => self::LATITUDE + 1, 'longitude' => self::LONGITUDE, 'recorded_at' => now()]);

        $this->actingAs($user)
            ->get(route('friends.index'))
            ->assertOk()
            ->assertSeeInOrder(['Bruno Vizinho', 'a menos de 1 km', 'Ana Perto', 'a cerca de 2 km'])
            ->assertDontSee('Carla Longe')
            ->assertDontSee('Davi Antigo')
            ->assertDontSee('Eva Mudou');
    }

    public function test_never_shows_coordinates_of_other_students(): void
    {
        $user = $this->userAt(self::LATITUDE, self::LONGITUDE);
        $this->userAt(-19.91234567, self::LONGITUDE);

        $this->actingAs($user)->get(route('friends.index'))->assertDontSee('-19.91234567');
    }

    public function test_asks_for_location_when_the_student_has_no_recent_position(): void
    {
        $user = $this->userAt(self::LATITUDE, self::LONGITUDE, recordedAt: now()->subDays(8));
        $this->userAt(self::LATITUDE, self::LONGITUDE, ['name' => 'Ana Perto']);

        $this->actingAs($user)
            ->get(route('friends.index'))
            ->assertSee('Localização desativada')
            ->assertDontSee('Ana Perto');
    }

    public function test_nearby_friends_open_the_conversation_and_pending_requests_are_marked(): void
    {
        $user = $this->userAt(self::LATITUDE, self::LONGITUDE);
        $friend = $this->userAt(self::LATITUDE + 0.01, self::LONGITUDE);
        $requested = $this->userAt(self::LATITUDE + 0.01, self::LONGITUDE);
        Friendship::factory()->accepted()->create(['requester_id' => $user->id, 'addressee_id' => $friend->id]);
        Friendship::factory()->create(['requester_id' => $user->id, 'addressee_id' => $requested->id]);

        $this->actingAs($user)
            ->get(route('friends.index'))
            ->assertSee(route('messages.show', $friend->id))
            ->assertSee('Pedido pendente');
    }

    public function test_sends_a_friend_request_to_someone_nearby(): void
    {
        $user = $this->userAt(self::LATITUDE, self::LONGITUDE);
        $nearby = $this->userAt(self::LATITUDE + 0.01, self::LONGITUDE);

        $this->actingAs($user)
            ->post(route('friends.store'), ['user_id' => $nearby->id])
            ->assertRedirect(route('friends.index'));

        $friendship = Friendship::sole();
        $this->assertSame($user->id, $friendship->requester_id);
        $this->assertSame($nearby->id, $friendship->addressee_id);
        $this->assertSame(FriendshipStatus::Pending, $friendship->status);
    }

    public function test_cannot_send_a_request_by_id_to_someone_who_is_not_nearby(): void
    {
        $user = $this->userAt(self::LATITUDE, self::LONGITUDE);
        $farAway = $this->userAt(self::LATITUDE + 1, self::LONGITUDE);
        $withoutLocation = User::factory()->create();

        foreach ([$farAway, $withoutLocation] as $other) {
            $this->actingAs($user)
                ->post(route('friends.store'), ['user_id' => $other->id])
                ->assertSessionHasErrors(['email' => 'Essa pessoa não está mais perto de você.']);
        }

        $this->assertSame(0, Friendship::count());
    }

    public function test_friends_list_opens_the_conversation_with_each_friend(): void
    {
        $user = User::factory()->create();
        $friend = User::factory()->create();
        Friendship::factory()->accepted()->create(['requester_id' => $friend->id, 'addressee_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('friends.index'))
            ->assertSee('href="'.route('messages.show', $friend->id).'"', false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function userAt(float $latitude, float $longitude, array $attributes = [], mixed $recordedAt = null): User
    {
        $user = User::factory()->create($attributes);

        UserLocation::factory()->for($user)->create([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'recorded_at' => $recordedAt ?? now(),
        ]);

        return $user;
    }
}
