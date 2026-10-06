<?php

namespace App\Http\Controllers\Enrollments;

use App\Actions\Enrollments\SaveLesson;
use App\Enums\LessonStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Enrollments\LessonRequest;
use App\Models\Enrollment;
use App\Models\Lesson;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class LessonController extends Controller
{
    public function create(Enrollment $enrollment): View
    {
        Gate::authorize('update', $enrollment);

        return view('enrollments.lessons.form', [
            'enrollment' => $enrollment,
            'lesson' => new Lesson(['starts_at' => now()->startOfHour(), 'class_count' => 1, 'status' => LessonStatus::Done]),
            'attendance' => null,
        ]);
    }

    public function store(LessonRequest $request, Enrollment $enrollment, SaveLesson $saveLesson): RedirectResponse
    {
        $saveLesson->handle($enrollment, $request->validated());

        return $this->backToEnrollment($enrollment, __('Aula registrada.'));
    }

    public function edit(Enrollment $enrollment, Lesson $lesson): View
    {
        Gate::authorize('update', $enrollment);

        return view('enrollments.lessons.form', [
            'enrollment' => $enrollment,
            'lesson' => $lesson,
            'attendance' => $lesson->attendances()->whereBelongsTo($enrollment)->first(),
        ]);
    }

    public function update(LessonRequest $request, Enrollment $enrollment, Lesson $lesson, SaveLesson $saveLesson): RedirectResponse
    {
        $saveLesson->handle($enrollment, $request->validated(), $lesson);

        return $this->backToEnrollment($enrollment, __('Aula atualizada.'));
    }

    public function destroy(Enrollment $enrollment, Lesson $lesson): RedirectResponse
    {
        Gate::authorize('update', $enrollment);

        $lesson->delete();

        return $this->backToEnrollment($enrollment, __('Aula removida.'));
    }

    private function backToEnrollment(Enrollment $enrollment, string $message): RedirectResponse
    {
        return redirect()->to(route('enrollments.show', $enrollment).'#aulas')->with('flash', $message);
    }
}
