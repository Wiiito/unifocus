<?php

namespace Database\Factories;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\ClassGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_group_id' => ClassGroup::factory(),
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(ActivityType::cases()),
            'max_points' => fake()->randomElement([10, 20, 25, 30]),
            'due_at' => fake()->dateTimeBetween('-1 week', '+1 month'),
        ];
    }
}
