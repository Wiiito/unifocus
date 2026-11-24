<?php

namespace Tests\Feature\Locations;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class UserLocationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->post(route('locations.store'), $this->payload())->assertRedirect(route('login'));

        $this->assertSame(0, UserLocation::count());
    }

    public function test_student_records_the_current_position(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeader('User-Agent', 'Mozilla/5.0 (Android) Unifocus')
            ->postJson(route('locations.store'), $this->payload())
            ->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.latitude', -23.55052)
            ->assertJsonPath('data.longitude', -46.633308)
            ->assertJsonPath('data.accuracy', 12.5)
            ->assertJsonPath('data.source', 'webview_gps');

        $location = UserLocation::sole();
        $this->assertSame($user->id, $location->user_id);
        $this->assertSame(-23.55052, $location->latitude);
        $this->assertSame(-46.633308, $location->longitude);
        $this->assertSame('Mozilla/5.0 (Android) Unifocus', $location->user_agent);
        $this->assertNotNull($location->recorded_at);
    }

    public function test_position_always_belongs_to_the_logged_in_student(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('locations.store'), [...$this->payload(), 'user_id' => $otherUser->id])
            ->assertCreated();

        $this->assertSame($user->id, UserLocation::sole()->user_id);
    }

    public function test_source_is_sanitized_and_defaults_to_browser_gps(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('locations.store'), [...$this->payload(), 'source' => 'app<script>_v2'])->assertCreated();
        $this->actingAs($user)->postJson(route('locations.store'), [...$this->payload(), 'source' => null])->assertCreated();

        $this->assertSame(['appscript_v2', UserLocation::DEFAULT_SOURCE], UserLocation::orderBy('id')->pluck('source')->all());
    }

    public function test_user_agent_is_truncated_to_255_characters(): void
    {
        $this->actingAs(User::factory()->create())
            ->withHeader('User-Agent', str_repeat('a', 300))
            ->postJson(route('locations.store'), $this->payload())
            ->assertCreated();

        $this->assertSame(255, strlen(UserLocation::sole()->user_agent));
    }

    public function test_requires_latitude_and_longitude(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('locations.store'), [])
            ->assertJsonValidationErrors(['latitude', 'longitude']);

        $this->assertSame(0, UserLocation::count());
    }

    #[TestWith(['latitude', 90.1, 'Latitude fora do intervalo válido (-90 a 90).'])]
    #[TestWith(['latitude', -90.1, 'Latitude fora do intervalo válido (-90 a 90).'])]
    #[TestWith(['longitude', 180.1, 'Longitude fora do intervalo válido (-180 a 180).'])]
    #[TestWith(['longitude', -180.1, 'Longitude fora do intervalo válido (-180 a 180).'])]
    public function test_rejects_coordinates_out_of_range(string $field, float $value, string $message): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('locations.store'), [...$this->payload(), $field => $value])
            ->assertJsonValidationErrors([$field => $message]);

        $this->assertSame(0, UserLocation::count());
    }

    public function test_rejects_negative_accuracy(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('locations.store'), [...$this->payload(), 'accuracy' => -1])
            ->assertJsonValidationErrors(['accuracy']);

        $this->assertSame(0, UserLocation::count());
    }

    public function test_rejects_accuracy_above_the_limit(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('locations.store'), [...$this->payload(), 'accuracy' => 5000.1])
            ->assertJsonValidationErrors(['accuracy' => 'Precisão insuficiente. Limite: 5000 m.']);

        $this->assertSame(0, UserLocation::count());
    }

    public function test_accepts_accuracy_exactly_at_the_limit(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('locations.store'), [...$this->payload(), 'accuracy' => 5000])
            ->assertCreated();

        $this->assertSame(5000.0, UserLocation::sole()->accuracy);
    }

    /**
     * @return array{latitude: float, longitude: float, accuracy: float, source: string}
     */
    private function payload(): array
    {
        return [
            'latitude' => -23.55052,
            'longitude' => -46.633308,
            'accuracy' => 12.5,
            'source' => 'webview_gps',
        ];
    }
}
