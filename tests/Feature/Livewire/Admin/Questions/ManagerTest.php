<?php

namespace Tests\Feature\Livewire\Admin\Questions;

use App\Enums\QuestionSource;
use App\Enums\QuestionType;
use App\Enums\ReviewStatus;
use App\Livewire\Admin\Questions\Manager;
use App\Models\Admin;
use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_a_multiple_choice_question_with_the_marked_correct_option(): void
    {
        $admin = Admin::factory()->create();
        $subject = Subject::factory()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(Manager::class)
            ->call('openCreateForm')
            ->set('form.subjectId', $subject->id)
            ->set('form.statement', 'Qual é a derivada de x²?')
            ->set('form.options.0.content', 'x')
            ->set('form.options.1.content', '2x')
            ->set('form.options.2.content', 'x²')
            ->set('form.options.3.content', '2')
            ->set('form.correctOption', 1)
            ->call('save')
            ->assertHasNoErrors();

        $question = Question::sole();
        $this->assertSame(QuestionSource::Manual, $question->source);
        $this->assertSame(ReviewStatus::Approved, $question->review_status);
        $this->assertSame($admin->id, $question->created_by_admin_id);
        $this->assertSame(['A', 'B', 'C', 'D'], $question->options->pluck('label')->all());
        $this->assertSame('2x', $question->correctOption->content);
    }

    public function test_true_false_question_stores_both_options(): void
    {
        $subject = Subject::factory()->create();

        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Manager::class)
            ->set('form.subjectId', $subject->id)
            ->set('form.type', QuestionType::TrueFalse->value)
            ->set('form.statement', 'Toda função contínua é derivável.')
            ->set('form.trueFalseAnswer', 'false')
            ->call('save')
            ->assertHasNoErrors();

        $options = Question::sole()->options;
        $this->assertCount(2, $options);
        $this->assertSame(__('Falso'), $options->firstWhere('is_correct', true)->content);
    }

    public function test_open_question_requires_the_explanation(): void
    {
        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Manager::class)
            ->set('form.subjectId', Subject::factory()->create()->id)
            ->set('form.type', QuestionType::Open->value)
            ->set('form.statement', 'Explique o teorema fundamental do cálculo.')
            ->call('save')
            ->assertHasErrors(['form.explanation' => 'required']);
    }

    public function test_rejects_a_statement_already_in_the_bank_ignoring_case_and_spaces(): void
    {
        $subject = Subject::factory()->create();
        Question::factory()->for($subject)->create(['statement' => 'Qual é a capital do Brasil?']);

        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Manager::class)
            ->set('form.subjectId', $subject->id)
            ->set('form.statement', '  qual é a   CAPITAL do brasil?')
            ->set('form.options.0.content', 'Brasília')
            ->set('form.options.1.content', 'Rio de Janeiro')
            ->set('form.options.2.content', 'São Paulo')
            ->set('form.options.3.content', 'Salvador')
            ->call('save')
            ->assertHasErrors(['form.statement']);
    }

    public function test_editing_options_keeps_the_answered_option_ids(): void
    {
        $question = Question::factory()->withOptions()->create();
        $answeredOption = $question->options()->first();
        $attempt = QuestionAttempt::factory()->for($question)->create(['question_option_id' => $answeredOption->id]);

        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Manager::class)
            ->call('openEditForm', $question->id)
            ->set('form.options.0.content', 'Texto corrigido')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Texto corrigido', $answeredOption->fresh()->content);
        $this->assertSame($answeredOption->id, $attempt->fresh()->question_option_id);
    }

    public function test_admin_approves_a_pending_question_from_the_list(): void
    {
        $question = Question::factory()->pending()->create();

        Livewire::actingAs(Admin::factory()->create(), 'admin')
            ->test(Manager::class)
            ->call('setReviewStatus', $question->id, ReviewStatus::Approved->value);

        $this->assertSame(ReviewStatus::Approved, $question->fresh()->review_status);
    }
}
