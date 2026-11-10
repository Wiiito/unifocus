<?php

namespace App\Http\Requests;

use App\Support\WebPush\P256;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Corpo de PushSubscription.toJSON() do navegador. As chaves são conferidas
 * pelo tamanho decodificado: uma chave inválida só falharia depois, na fila,
 * ao cifrar a primeira notificação.
 */
class StorePushSubscriptionRequest extends FormRequest
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
            'endpoint' => ['required', 'string', 'url:https', 'max:1000'],
            'keys.p256dh' => ['required', 'string', 'max:255', $this->decodesTo(65)],
            'keys.auth' => ['required', 'string', 'max:255', $this->decodesTo(16)],
        ];
    }

    private function decodesTo(int $bytes): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($bytes): void {
            if (! is_string($value) || strlen(P256::base64UrlDecode($value)) !== $bytes) {
                $fail(__('Chave de inscrição push inválida.'));
            }
        };
    }
}
