<?php

namespace Database\Factories;

use App\Enums\ClassGroupStatus;
use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassGroup>
 */
class ClassGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'created_by_user_id' => User::factory(),
            'name' => 'Turma '.fake()->randomLetter(),
            'is_personal' => true,
            'total_points' => 100,
            'passing_percent' => 60,
            'max_absence_percent' => 25,
            'status' => ClassGroupStatus::InProgress,
        ];
    }

    /**
     * Sem regras próprias: herda da instituição (ou fica sem regras).
     */
    public function withoutOwnRules(): static
    {
        return $this->state(fn (): array => [
            'total_points' => null,
            'passing_percent' => null,
            'max_absence_percent' => null,
        ]);
    }
}
