<?php

namespace App\Http\Requests\Enrollments;

use App\Enums\ActivityType;
use App\Enums\SubmissionStatus;
use App\Http\Requests\Enrollments\Concerns\ValidatesWeight;
use Illuminate\Validation\Rule;

class ActivityRequest extends EnrollmentContentRequest
{
    use ValidatesWeight;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::enum(ActivityType::class)],
            'max_points' => ['nullable', 'numeric', 'gt:0', 'max:1000'],
            'weight' => $this->weightRules(),
            'due_at' => ['nullable', 'date'],
            'submission_status' => ['required', Rule::enum(SubmissionStatus::class)],

            /** Sem valor da atividade não há o que pontuar. */
            'points' => ['exclude_without:max_points', 'nullable', 'numeric', 'min:0', 'lte:max_points'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => __('título'),
            'type' => __('tipo'),
            'max_points' => __('valor da atividade'),
            'due_at' => __('prazo'),
            'submission_status' => __('entrega'),
            'points' => __('nota obtida'),
            'weight' => __('peso'),
        ];
    }
}
