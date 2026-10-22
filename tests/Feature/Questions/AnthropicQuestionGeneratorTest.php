<?php

namespace Tests\Feature\Questions;

use App\Contracts\QuestionGenerator;
use App\Enums\AiGenerationStatus;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionSource;
use App\Enums\QuestionType;
use App\Enums\ReviewStatus;
use App\Jobs\GenerateQuestions;
use App\Models\Admin;
use App\Models\AiGeneration;
use App\Models\Question;
use App\Models\Subject;
use App\Support\Questions\AnthropicQuestionGenerator;
use App\Support\Questions\GeneratedQuestionParser;
use App\Support\Questions\OpenRouterQuestionGenerator;
use App\Support\Questions\QuestionGenerationRequest;
use App\Support\Questions\UnavailableQuestionGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class AnthropicQuestionGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        /** Fixa o provedor: o .env da máquina pode escolher outro (e ter chave real). */
        config([
            'ai.provider' => 'anthropic',
            'ai.anthropic.key' => 'test-key',
            'ai.openrouter.key' => null,
            'ai.questions.enabled' => true,
        ]);

        /** Nenhuma chamada sai para a internet: requisição não simulada falha o teste. */
        Http::preventStrayRequests();
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     */
    private function fakeApi(array $questions, int $status = 200): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response(
            $status === 200
                ? ['content' => [['type' => 'text', 'text' => json_encode(['questions' => $questions])]], 'usage' => ['input_tokens' => 200, 'output_tokens' => 400]]
                : ['error' => ['message' => 'overloaded']],
            $status,
        )]);
    }

    /**
     * @return array<string, mixed>
     */
    private function multipleChoice(string $statement = 'Qual estrutura segue a política LIFO?'): array
    {
        return [
            'statement' => $statement,
            'topic' => 'Pilhas',
            'explanation' => 'Pilhas removem o último elemento inserido.',
            'options' => [
                ['content' => 'Fila', 'is_correct' => false],
                ['content' => 'Pilha', 'is_correct' => true],
                ['content' => 'Árvore', 'is_correct' => false],
                ['content' => 'Grafo', 'is_correct' => false],
            ],
        ];
    }

    public function test_generates_pending_ai_questions_and_logs_the_call(): void
    {
        $this->fakeApi([$this->multipleChoice()]);
        $subject = Subject::factory()->create();
        $admin = Admin::factory()->create();

        $questions = app(QuestionGenerator::class)->generate(new QuestionGenerationRequest($subject, $admin, count: 1));

        $question = $questions->sole();
        $this->assertSame(QuestionSource::Ai, $question->source);
        $this->assertSame(ReviewStatus::Pending, $question->review_status);
        $this->assertSame($subject->id, $question->subject_id);
        $this->assertSame($admin->id, $question->created_by_admin_id);
        $this->assertSame(['Fila', 'Pilha', 'Árvore', 'Grafo'], $question->options->pluck('content')->all());
        $this->assertSame('Pilha', $question->correctOption->content);

        $generation = $question->aiGeneration;
        $this->assertSame(AiGenerationStatus::Succeeded, $generation->status);
        $this->assertSame(200, $generation->tokens_input);
        $this->assertSame(400, $generation->tokens_output);
        $this->assertSame($admin->id, $generation->requestedBy->id);
        $this->assertSame(64, strlen($generation->prompt_hash));

        Http::assertSent(fn ($request) => $request->hasHeader('x-api-key', 'test-key')
            && $request['model'] === config('ai.anthropic.model')
            && str_contains($request['messages'][0]['content'], $subject->name));
    }

    public function test_skips_statements_that_already_exist(): void
    {
        $subject = Subject::factory()->create();
        Question::factory()->for($subject)->create(['statement' => 'Qual estrutura segue a política LIFO?']);
        $this->fakeApi([$this->multipleChoice('qual estrutura  segue a política lifo?'), $this->multipleChoice('Qual estrutura segue a política FIFO?')]);

        $questions = app(QuestionGenerator::class)->generate(new QuestionGenerationRequest($subject));

        $this->assertCount(1, $questions);
        $this->assertSame('Qual estrutura segue a política FIFO?', $questions->sole()->statement);
    }

    public function test_api_failure_marks_the_generation_as_failed_and_saves_nothing(): void
    {
        $this->fakeApi([], 529);
        $subject = Subject::factory()->create();

        try {
            app(QuestionGenerator::class)->generate(new QuestionGenerationRequest($subject));
            $this->fail('A exceção da API deveria ser propagada.');
        } catch (RequestException) {
            // esperado
        }

        $this->assertSame(0, Question::count());
        $generation = AiGeneration::sole();
        $this->assertSame(AiGenerationStatus::Failed, $generation->status);
        $this->assertNotNull($generation->error);
    }

    public function test_response_without_valid_questions_fails_the_generation(): void
    {
        $this->fakeApi([['statement' => 'Sem gabarito', 'options' => [['content' => 'A', 'is_correct' => false], ['content' => 'B', 'is_correct' => false]]]]);

        $this->expectException(RuntimeException::class);

        try {
            app(QuestionGenerator::class)->generate(new QuestionGenerationRequest(Subject::factory()->create()));
        } finally {
            $this->assertSame(0, Question::count());
            $this->assertSame(AiGenerationStatus::Failed, AiGeneration::sole()->status);
        }
    }

    public function test_container_resolves_the_real_generator_only_when_enabled_and_configured(): void
    {
        $this->assertInstanceOf(AnthropicQuestionGenerator::class, app(QuestionGenerator::class));

        config(['ai.questions.enabled' => false]);
        $this->assertInstanceOf(UnavailableQuestionGenerator::class, app(QuestionGenerator::class));

        config(['ai.questions.enabled' => true, 'ai.anthropic.key' => null]);
        $this->assertInstanceOf(UnavailableQuestionGenerator::class, app(QuestionGenerator::class));
        $this->assertFalse(app(QuestionGenerator::class)->isAvailable());
    }

    public function test_openrouter_provider_generates_questions_and_records_its_cost(): void
    {
        config(['ai.provider' => 'openrouter', 'ai.openrouter.key' => 'or-key', 'ai.openrouter.model' => 'openai/gpt-4o-mini', 'ai.anthropic.key' => null]);
        Http::fake(['openrouter.ai/*' => Http::response([
            'choices' => [['message' => ['content' => json_encode(['questions' => [$this->multipleChoice()]])]]],
            'usage' => ['prompt_tokens' => 150, 'completion_tokens' => 300, 'cost' => 0.0123],
        ])]);
        $subject = Subject::factory()->create();

        $this->assertInstanceOf(OpenRouterQuestionGenerator::class, app(QuestionGenerator::class));

        $question = app(QuestionGenerator::class)->generate(new QuestionGenerationRequest($subject))->sole();

        $this->assertSame(ReviewStatus::Pending, $question->review_status);
        $generation = $question->aiGeneration;
        $this->assertSame('openrouter', $generation->provider);
        $this->assertSame('openai/gpt-4o-mini', $generation->model);
        $this->assertSame(150, $generation->tokens_input);
        $this->assertSame(300, $generation->tokens_output);
        $this->assertSame(1, $generation->cost_cents);

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer or-key')
            && $request['model'] === 'openai/gpt-4o-mini'
            && $request['messages'][0]['role'] === 'system');
    }

    public function test_openrouter_without_key_is_unavailable_even_if_anthropic_is_configured(): void
    {
        config(['ai.provider' => 'openrouter', 'ai.openrouter.key' => null]);

        $this->assertInstanceOf(UnavailableQuestionGenerator::class, app(QuestionGenerator::class));
    }

    public function test_parser_accepts_fenced_json_and_enforces_rules_per_type(): void
    {
        $parser = new GeneratedQuestionParser;
        $fenced = "```json\n".json_encode(['questions' => [$this->multipleChoice()]])."\n```";

        $this->assertCount(1, $parser->parse($fenced, QuestionType::MultipleChoice));
        $this->assertSame([], $parser->parse('não é json', QuestionType::MultipleChoice));
        $this->assertSame([], $parser->parse($fenced, QuestionType::TrueFalse), 'quatro alternativas não servem para V/F');

        $numeric = json_encode(['questions' => [
            ['statement' => 'Quanto é 2+2?', 'options' => [['content' => '4', 'is_correct' => true]]],
            ['statement' => 'Quanto é 3+3?', 'options' => [['content' => 'seis', 'is_correct' => true]]],
        ]]);
        $this->assertCount(1, $parser->parse($numeric, QuestionType::Numeric));

        $open = json_encode(['questions' => [
            ['statement' => 'Explique recursão.', 'explanation' => 'Uma função que chama a si mesma.', 'options' => [['content' => 'x', 'is_correct' => true]]],
            ['statement' => 'Sem gabarito.'],
        ]]);
        $parsed = $parser->parse($open, QuestionType::Open);
        $this->assertCount(1, $parsed);
        $this->assertSame([], $parsed[0]['options']);
    }

    public function test_job_asks_the_generator_with_the_given_request(): void
    {
        $this->fakeApi([$this->multipleChoice()]);
        $subject = Subject::factory()->create();

        GenerateQuestions::dispatchSync($subject, null, 3, QuestionType::MultipleChoice, QuestionDifficulty::Hard, 'Pilhas');

        $question = Question::sole();
        $this->assertSame(QuestionDifficulty::Hard, $question->difficulty);
        Http::assertSent(fn ($request) => str_contains($request['messages'][0]['content'], 'Gere 3 questões')
            && str_contains($request['messages'][0]['content'], 'Pilhas'));
    }

    public function test_generate_command_dispatches_by_subject_code(): void
    {
        Queue::fake();
        $subject = Subject::factory()->create(['code' => 'INF102']);

        $this->artisan('questions:generate', ['subject' => 'INF102', '--count' => 3, '--queue' => true])->assertSuccessful();

        Queue::assertPushed(GenerateQuestions::class, fn (GenerateQuestions $job) => $job->subject->is($subject) && $job->count === 3);
    }

    public function test_generate_command_refuses_when_ai_is_off(): void
    {
        config(['ai.questions.enabled' => false]);

        $this->artisan('questions:generate', ['subject' => '1'])->assertFailed();
    }
}
