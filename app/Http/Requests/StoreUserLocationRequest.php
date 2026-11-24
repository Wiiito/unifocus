<?php

namespace App\Http\Requests;

use App\Models\UserLocation;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Posição enviada pelo LocationTracker. O dono do registro é sempre quem está
 * logado: um user_id vindo no corpo é ignorado.
 */
class StoreUserLocationRequest extends FormRequest
{
    /** Acima disso o GPS não está confiável o bastante para guardar a posição. */
    public const MAX_ACCURACY_METERS = 5000;

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
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:'.self::MAX_ACCURACY_METERS],
            'source' => ['required', 'string', 'max:30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.between' => __('Latitude fora do intervalo válido (-90 a 90).'),
            'longitude.between' => __('Longitude fora do intervalo válido (-180 a 180).'),
            'accuracy.max' => __('Precisão insuficiente. Limite: :max m.'),
        ];
    }

    /**
     * Origem livre vinda do front: fica só com letras, números, "_" e "-".
     */
    protected function prepareForValidation(): void
    {
        $source = is_string($this->input('source')) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $this->input('source')) : '';

        $this->merge(['source' => $source !== '' ? $source : UserLocation::DEFAULT_SOURCE]);
    }
}
