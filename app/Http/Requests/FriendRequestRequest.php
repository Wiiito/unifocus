<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Pedido de amizade por e-mail exato: buscar por nome expõe quem usa a
 * plataforma, então só encontra quem a pessoa já sabe quem é. A outra porta
 * é o user_id de alguém da lista de pessoas próximas, conferido no controller.
 */
class FriendRequestRequest extends FormRequest
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
            'email' => ['required_without:user_id', 'nullable', 'string', 'email', 'max:255', 'exists:users,email'],
            'user_id' => ['required_without:email', 'nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.exists' => __('Não encontramos ninguém com este e-mail.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => __('e-mail'),
        ];
    }
}
