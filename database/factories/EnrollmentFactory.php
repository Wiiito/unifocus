<?php

namespace Database\Factories;

use App\Enums\EnrollmentStatus;
use App\Models\ClassGroup;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
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
            'user_id' => User::factory(),
            'status' => EnrollmentStatus::Active,
            'enrolled_at' => now(),
        ];
    }

    /**
     * Matrícula do próprio dono da turma pessoal (o caso real de hoje).
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->id,
            'class_group_id' => ClassGroup::factory()->state(['created_by_user_id' => $user->id]),
        ]);
    }
}
