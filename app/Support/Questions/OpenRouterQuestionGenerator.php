<?php

namespace App\Support\Questions;

use Illuminate\Support\Facades\Http;

/**
 * Gera questões pelo OpenRouter (API compatível com a da OpenAI), que dá
 * acesso a modelos de vários provedores com uma só chave. O custo vem na
 * própria resposta (usage.cost, em dólares), então não depende de tabela de
 * preços local.
 */
class OpenRouterQuestionGenerator extends LlmQuestionGenerator
{
    public function isAvailable(): bool
    {
        return filled(config('ai.openrouter.key'));
    }

    protected function provider(): string
    {
        return 'openrouter';
    }

    protected function model(): string
    {
        return config('ai.openrouter.model');
    }

    protected function complete(string $system, string $user): QuestionCompletion
    {
        $response = Http::baseUrl(config('ai.openrouter.base_url'))
            ->withToken(config('ai.openrouter.key'))
            ->withHeaders(array_filter([
                'HTTP-Referer' => config('ai.openrouter.referer'),
                'X-Title' => config('ai.openrouter.title'),
            ]))
            ->timeout((int) config('ai.openrouter.timeout'))
            ->acceptJson()
            ->asJson()
            ->post('/chat/completions', [
                'model' => $this->model(),
                'max_tokens' => (int) config('ai.openrouter.max_tokens'),
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
                'usage' => ['include' => true],
            ])
            ->throw();

        $cost = $response->json('usage.cost');

        return new QuestionCompletion(
            text: (string) $response->json('choices.0.message.content', ''),
            inputTokens: $response->json('usage.prompt_tokens'),
            outputTokens: $response->json('usage.completion_tokens'),
            costCents: is_numeric($cost) ? (int) round($cost * 100) : null,
        );
    }
}
