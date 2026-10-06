<?php

namespace App\Http\Requests\Enrollments\Concerns;

/**
 * Peso de uma nota no total do período. Campo opcional: em branco vale 1.
 */
trait ValidatesWeight
{
    public const DEFAULT_WEIGHT = 1;

    protected function prepareForValidation(): void
    {
        if (blank($this->input('weight'))) {
            $this->merge(['weight' => self::DEFAULT_WEIGHT]);
        }
    }

    /**
     * @return array<int, string>
     */
    protected function weightRules(): array
    {
        return ['required', 'numeric', 'gt:0', 'max:99.99'];
    }
}
