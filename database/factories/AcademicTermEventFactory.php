<?php

namespace Database\Factories;

use App\Enums\TermEventType;
use App\Models\AcademicTerm;
use App\Models\AcademicTermEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicTermEvent>
 */
class AcademicTermEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsOn = fake()->dateTimeBetween('now', '+2 months');

        return [
            'academic_term_id' => AcademicTerm::factory(),
            'title' => fake()->words(3, true),
            'type' => fake()->randomElement(TermEventType::cases()),
            'starts_on' => $startsOn,
            'ends_on' => (clone $startsOn)->modify('+4 days'),
        ];
    }
}
