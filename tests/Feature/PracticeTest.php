<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Models\Enrollment;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PracticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_an_approved_question_from_an_enrolled_subject(): void
    {
        $user = User::factory()->create();
        $enrollment = Enrollment::factory()->forUser($user)->create();
        $question = Question::factory()->withOptions()->for($enrollment->subject())->create();
        $pendingQuestion = Question::factory()->pending()->for($enrollment->subject())->create();
        $otherSubjectQuestion = Question::factory()->create();

        $this->actingAs($user)
            ->get(route('practice.show'))
            ->assertOk()
            ->assertSee($question->statement)
            ->assertDontSee($pendingQuestion->statement)
            ->assertDontSee($otherSubjectQuestion->statement);
    }

    public function test_answering_correctly_records_the_attempt_and_updates_the_statistics(): void
    {
        $user = User::factory()->create();
        $question = Question::factory()->withOptions()->create();
        $correctOption = $question->options()->firstWhere('is_correct', true);

        $this->actingAs($user)
            ->post(route('practice.store', $question), ['option_id' => $correctOption->id])
            ->assertRedirect()
            ->assertSessionHas('attempt_id');

        $this->assertTrue($user->questionAttempts()->sole()->is_correct);
        $question->refresh();
        $this->assertSame(1, $question->times_used);
        $this->assertSame(100.0, $question->correct_rate);
    }

    public function test_wrong_answer_lowers_the_correct_rate(): void
    {
        $question = Question::factory()->withOptions()->create();
        $wrongOption = $question->options()->firstWhere('is_correct', false);
        $correctOption = $question->options()->firstWhere('is_correct', true);

        $this->actingAs(User::factory()->create())->post(route('practice.store', $question), ['option_id' => $correctOption->id]);
        $this->actingAs(User::factory()->create())->post(route('practice.store', $question), ['option_id' => $wrongOption->id]);

        $this->assertSame(50.0, $question->fresh()->correct_rate);
    }

    public function test_numeric_answer_accepts_comma_as_decimal_separator(): void
    {
        $user = User::factory()->create();
        $question = Question::factory()->create(['type' => QuestionType::Numeric]);
        QuestionOption::factory()->for($question)->create(['content' => '3.14', 'is_correct' => true]);

        $this->actingAs($user)->post(route('practice.store', $question), ['answer_text' => '3,14']);

        $this->assertTrue($user->questionAttempts()->sole()->is_correct);
    }

    public function test_rejects_an_option_from_another_question(): void
    {
        $question = Question::factory()->withOptions()->create();
        $foreignOption = QuestionOption::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('practice.store', $question), ['option_id' => $foreignOption->id])
            ->assertSessionHasErrors('option_id');
    }

    public function test_unapproved_question_cannot_be_answered(): void
    {
        $question = Question::factory()->pending()->withOptions()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('practice.store', $question), ['option_id' => $question->options()->first()->id])
            ->assertNotFound();
    }
}
