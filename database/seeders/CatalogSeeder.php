<?php

namespace Database\Seeders;

use App\Actions\Questions\SaveQuestion;
use App\Enums\QuestionSource;
use App\Enums\QuestionType;
use App\Enums\ReviewStatus;
use App\Enums\TermEventType;
use App\Models\Institution;
use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Catálogo de exemplo para desenvolvimento: o que os admins cadastrariam
 * pelo painel (instituição, período com calendário, matérias e questões).
 */
class CatalogSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(SaveQuestion $saveQuestion): void
    {
        $institution = Institution::factory()->create([
            'name' => 'Universidade Exemplo',
            'slug' => 'universidade-exemplo',
            'passing_percent' => 65,
        ]);

        $term = $institution->academicTerms()->create([
            'name' => today()->format('Y').'.'.(today()->month <= 6 ? 1 : 2),
            'starts_on' => today()->subMonths(2)->startOfMonth()->toDateString(),
            'ends_on' => today()->addMonths(3)->endOfMonth()->toDateString(),
        ]);

        $term->events()->createMany([
            ['title' => 'Semana de provas P1', 'type' => TermEventType::ExamWeek, 'starts_on' => today()->addWeeks(2)->startOfWeek(), 'ends_on' => today()->addWeeks(2)->endOfWeek()],
            ['title' => 'Recesso', 'type' => TermEventType::Recess, 'starts_on' => today()->addMonth()->startOfWeek(), 'ends_on' => today()->addMonth()->startOfWeek()->addDays(4)],
        ]);

        $calculus = Subject::factory()->for($institution)->create(['name' => 'Cálculo I', 'code' => 'MAT101', 'color' => '#2196f3']);
        $algorithms = Subject::factory()->for($institution)->create(['name' => 'Algoritmos', 'code' => 'INF110', 'color' => '#2b5bb5']);
        Subject::factory()->create(['name' => 'Inglês Instrumental', 'code' => 'ING001', 'color' => '#21638d']);

        $questions = [
            [$calculus, QuestionType::MultipleChoice, 'Qual é a derivada de f(x) = x²?', ['x', '2x', 'x²', '2'], 1, 'Pela regra da potência, d/dx xⁿ = n·xⁿ⁻¹.'],
            [$calculus, QuestionType::TrueFalse, 'Toda função contínua é derivável.', [], false, 'A função |x| é contínua em 0, mas não é derivável nesse ponto.'],
            [$algorithms, QuestionType::MultipleChoice, 'Qual a complexidade da busca binária em um vetor ordenado?', ['O(1)', 'O(log n)', 'O(n)', 'O(n log n)'], 1, 'A cada passo o espaço de busca cai pela metade.'],
        ];

        foreach ($questions as [$subject, $type, $statement, $options, $answer, $explanation]) {
            $saveQuestion->handle(
                [
                    'subject_id' => $subject->id,
                    'type' => $type,
                    'statement' => $statement,
                    'explanation' => $explanation,
                    'source' => QuestionSource::Manual,
                    'review_status' => ReviewStatus::Approved,
                ],
                $type === QuestionType::TrueFalse
                    ? [['content' => __('Verdadeiro'), 'is_correct' => $answer], ['content' => __('Falso'), 'is_correct' => ! $answer]]
                    : collect($options)->map(fn (string $content, int $index): array => ['content' => $content, 'is_correct' => $index === $answer])->all(),
            );
        }
    }
}
