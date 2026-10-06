<?php

namespace App\Http\Requests;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnswerQuestionRequest extends FormRequest
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
        /** @var Question $question */
        $question = $this->route('question');

        if ($question->type->usesOptions()) {
            return [
                'option_id' => ['required', 'integer', Rule::exists('question_options', 'id')->where('question_id', $question->id)],
            ];
        }

        return [
            'answer_text' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'option_id' => __('alternativa'),
            'answer_text' => __('resposta'),
        ];
    }
}
