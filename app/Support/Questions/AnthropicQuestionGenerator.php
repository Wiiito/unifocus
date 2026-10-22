<?php

namespace App\Support\Questions;

use Illuminate\Support\Facades\Http;

/**
 * Gera questões direto pela API de mensagens da Anthropic.
 */
class AnthropicQuestionGenerator extends LlmQuestionGenerator
{
    public function isAvailable(): bool
    {
        return filled(config('ai.anthropic.key'));
    }

    protected function provider(): string
    {
        return 'anthropic';
    }

    protected function model(): string
    {
        return config('ai.anthropic.model');
    }

    protected function complete(string $system, string $user): QuestionCompletion
    {
        $response = Http::baseUrl(config('ai.anthropic.base_url'))
            ->withHeaders([
                'x-api-key' => config('ai.anthropic.key'),
                'anthropic-version' => config('ai.anthropic.version'),
            ])
            ->timeout((int) config('ai.anthropic.timeout'))
            ->acceptJson()
            ->asJson()
            ->post('/v1/messages', [
                'model' => $this->model(),
                'max_tokens' => (int) config('ai.anthropic.max_tokens'),
                'system' => $system,
                'messages' => [['role' => 'user', 'content' => $user]],
            ])
            ->throw();

        $input = (int) $response->json('usage.input_tokens');
        $output = (int) $response->json('usage.output_tokens');
        $pricing = config('ai.anthropic.pricing');

        return new QuestionCompletion(
            text: collect($response->json('content', []))->where('type', 'text')->pluck('text')->implode(''),
            inputTokens: $input,
            outputTokens: $output,
            costCents: (int) round(($input * $pricing['input_cents_per_million'] + $output * $pricing['output_cents_per_million']) / 1_000_000),
        );
    }
}
