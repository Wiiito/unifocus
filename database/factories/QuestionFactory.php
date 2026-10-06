<?php

namespace Database\Factories;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionSource;
use App\Enums\QuestionType;
use App\Enums\ReviewStatus;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
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
            'statement' => fake()->unique()->sentence(12).'?',
            'type' => QuestionType::MultipleChoice,
            'difficulty' => fake()->randomElement(QuestionDifficulty::cases()),
            'explanation' => fake()->optional()->paragraph(),
            'source' => QuestionSource::Manual,
            'review_status' => ReviewStatus::Approved,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['review_status' => ReviewStatus::Pending]);
    }

    /**
     * Quatro alternativas, a primeira correta.
     */
    public function withOptions(): static
    {
        return $this->afterCreating(function (Question $question): void {
            foreach (range(0, 3) as $position) {
                QuestionOption::factory()->for($question)->create([
                    'label' => chr(ord('A') + $position),
                    'position' => $position,
                    'is_correct' => $position === 0,
                ]);
            }
        });
    }
}
