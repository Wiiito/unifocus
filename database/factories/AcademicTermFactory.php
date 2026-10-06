<?php

namespace Database\Factories;

use App\Models\AcademicTerm;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicTerm>
 */
class AcademicTermFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsOn = fake()->dateTimeBetween('-2 months', 'now');

        return [
            'institution_id' => Institution::factory(),
            'name' => fake()->unique()->numerify('20##.#'),
            'starts_on' => $startsOn,
            'ends_on' => (clone $startsOn)->modify('+5 months'),
        ];
    }
}
