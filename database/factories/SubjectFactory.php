<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'code' => fake()->unique()->bothify('???###'),
            'description' => fake()->optional()->paragraph(),
            'credits' => fake()->numberBetween(1, 10),
            'workload_hours' => fake()->randomElement([30, 40, 60, 80]),
            'color' => fake()->hexColor(),
        ];
    }
}
