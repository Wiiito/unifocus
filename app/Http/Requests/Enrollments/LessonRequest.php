<?php

namespace App\Http\Requests\Enrollments;

use App\Enums\AttendanceStatus;
use App\Enums\LessonStatus;
use Illuminate\Validation\Rule;

class LessonRequest extends EnrollmentContentRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'topic' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'starts_at' => ['required', 'date'],
            'class_count' => ['required', 'integer', 'min:1', 'max:10'],
            'status' => ['required', Rule::enum(LessonStatus::class)],
            'attendance_status' => ['nullable', Rule::enum(AttendanceStatus::class)],
            'justification' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => __('título'),
            'topic' => __('conteúdo'),
            'starts_at' => __('data e hora'),
            'class_count' => __('quantidade de aulas'),
            'attendance_status' => __('presença'),
            'justification' => __('justificativa'),
        ];
    }
}
