<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserLocation>
 */
class UserLocationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'accuracy' => fake()->randomFloat(2, 3, 100),
            'source' => UserLocation::DEFAULT_SOURCE,
            'user_agent' => fake()->userAgent(),
            'recorded_at' => now(),
        ];
    }
}
