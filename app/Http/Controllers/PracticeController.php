<?php

namespace App\Http\Controllers;

use App\Actions\Questions\AnswerQuestion;
use App\Enums\ReviewStatus;
use App\Http\Requests\AnswerQuestionRequest;
use App\Models\Question;
use App\Support\Students\PracticeQuestionPicker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    /**
     * Próxima questão para praticar (ou o resultado da última resposta).
     */
    public function show(Request $request, PracticeQuestionPicker $picker): View
    {
        $user = $request->user();
        $subjectId = $request->integer('subject') ?: null;

        $lastAttempt = $request->session()->has('attempt_id')
            ? $user->questionAttempts()->with(['question.options', 'question.subject'])->find($request->session()->get('attempt_id'))
            : null;

        return view('practice.show', [
            'lastAttempt' => $lastAttempt,
            'question' => $lastAttempt ? null : $picker->next($user, $subjectId)?->load(['options', 'subject']),
            'subjects' => $user->enrollments()->ongoing()->withSummary()->get()
                ->map->subject()->unique('id')->sortBy('name')->values(),
            'subjectId' => $subjectId,
        ]);
    }

    public function store(AnswerQuestionRequest $request, Question $question, AnswerQuestion $answerQuestion): RedirectResponse
    {
        abort_unless($question->review_status === ReviewStatus::Approved, 404);

        $attempt = $answerQuestion->handle(
            $request->user(),
            $question,
            $request->integer('option_id') ?: null,
            $request->input('answer_text'),
        );

        return redirect()->route('practice.show', ['subject' => $request->integer('subject') ?: null])
            ->with('attempt_id', $attempt->id);
    }
}
