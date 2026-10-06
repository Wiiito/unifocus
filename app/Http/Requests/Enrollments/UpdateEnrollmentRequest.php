<?php

namespace App\Http\Requests\Enrollments;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Subject;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateEnrollmentRequest extends ClassGroupSettingsRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('update', $this->enrollment());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->classGroupRules(),
            'status' => ['required', Rule::enum(EnrollmentStatus::class)],
        ];
    }

    protected function subject(): ?Subject
    {
        return $this->enrollment()->classGroup->subject;
    }

    private function enrollment(): Enrollment
    {
        return $this->route('enrollment');
    }
}
