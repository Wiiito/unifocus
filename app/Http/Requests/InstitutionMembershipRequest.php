<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstitutionMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'institution_id' => [
                'required',
                'integer',
                Rule::exists('institutions', 'id')->whereNull('deleted_at'),
                Rule::unique('institution_members', 'institution_id')->where('user_id', $this->user()->id),
            ],
            'registration_code' => ['nullable', 'string', 'max:40'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'institution_id.unique' => __('Você já está vinculado a esta instituição.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'institution_id' => __('instituição'),
            'registration_code' => __('matrícula'),
        ];
    }
}
