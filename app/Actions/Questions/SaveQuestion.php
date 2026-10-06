<?php

namespace App\Actions\Questions;

use App\Models\Question;
use Illuminate\Support\Facades\DB;

/**
 * Ponto único de escrita no banco de questões: o painel admin usa hoje e o
 * futuro gerador por IA vai usar também, com as mesmas regras.
 */
class SaveQuestion
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array{content: string, is_correct: bool}>  $options  Alternativas já na ordem de exibição.
     */
    public function handle(array $attributes, array $options, ?Question $question = null): Question
    {
        return DB::transaction(function () use ($attributes, $options, $question): Question {
            $question ??= new Question;
            $question->fill($attributes)->save();

            $this->syncOptions($question, $options);

            return $question;
        });
    }

    /**
     * Atualiza por posição em vez de apagar e recriar: as respostas já dadas
     * continuam apontando para a mesma alternativa depois de uma edição.
     *
     * @param  array<int, array{content: string, is_correct: bool}>  $options
     */
    private function syncOptions(Question $question, array $options): void
    {
        $existing = $question->options()->get()->values();

        foreach (array_values($options) as $position => $option) {
            ($existing->get($position) ?? $question->options()->make())
                ->fill([
                    'label' => count($options) > 1 ? chr(ord('A') + $position) : null,
                    'content' => $option['content'],
                    'is_correct' => $option['is_correct'],
                    'position' => $position,
                ])
                ->save();
        }

        $existing->slice(count($options))->each->delete();
    }
}
