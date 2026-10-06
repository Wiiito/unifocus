<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Institution>
 */
class InstitutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'document' => fake()->optional()->numerify('##.###.###/####-##'),
            'total_points' => Institution::DEFAULT_TOTAL_POINTS,
            'passing_percent' => Institution::DEFAULT_PASSING_PERCENT,
            'max_absence_percent' => Institution::DEFAULT_MAX_ABSENCE_PERCENT,
        ];
    }
}
