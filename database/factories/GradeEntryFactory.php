<?php

namespace Database\Factories;

use App\Enums\GradeKind;
use App\Models\Enrollment;
use App\Models\GradeEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeEntry>
 */
class GradeEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maxPoints = fake()->randomElement([10, 20, 30]);

        return [
            'enrollment_id' => Enrollment::factory(),
            'label' => fake()->words(2, true),
            'kind' => GradeKind::Manual,
            'points' => fake()->randomFloat(2, 0, $maxPoints),
            'max_points' => $maxPoints,
            'recorded_at' => now(),
        ];
    }
}
