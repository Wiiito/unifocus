<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonAttendance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonAttendance>
 */
class LessonAttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'enrollment_id' => Enrollment::factory(),
            'status' => AttendanceStatus::Present,
            'recorded_at' => now(),
        ];
    }
}
