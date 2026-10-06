<?php

namespace Database\Factories;

use App\Enums\LessonStatus;
use App\Models\ClassGroup;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
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
            'topic' => fake()->optional()->words(3, true),
            'starts_at' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'class_count' => 1,
            'status' => LessonStatus::Done,
        ];
    }
}
