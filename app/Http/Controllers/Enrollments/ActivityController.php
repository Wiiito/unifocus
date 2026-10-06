<?php

namespace App\Http\Controllers\Enrollments;

use App\Actions\Enrollments\SaveActivity;
use App\Enums\ActivityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Enrollments\ActivityRequest;
use App\Models\Activity;
use App\Models\Enrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ActivityController extends Controller
{
    public function create(Enrollment $enrollment): View
    {
        Gate::authorize('update', $enrollment);

        return view('enrollments.activities.form', [
            'enrollment' => $enrollment,
            'activity' => new Activity(['type' => ActivityType::Homework]),
            'submission' => null,
            'gradeEntry' => null,
        ]);
    }

    public function store(ActivityRequest $request, Enrollment $enrollment, SaveActivity $saveActivity): RedirectResponse
    {
        $saveActivity->handle($enrollment, $request->validated());

        return $this->backToEnrollment($enrollment, __('Atividade registrada.'));
    }

    public function edit(Enrollment $enrollment, Activity $activity): View
    {
        Gate::authorize('update', $enrollment);

        return view('enrollments.activities.form', [
            'enrollment' => $enrollment,
            'activity' => $activity,
            'submission' => $activity->submissions()->whereBelongsTo($enrollment)->first(),
            'gradeEntry' => $activity->gradeEntries()->whereBelongsTo($enrollment)->first(),
        ]);
    }

    public function update(ActivityRequest $request, Enrollment $enrollment, Activity $activity, SaveActivity $saveActivity): RedirectResponse
    {
        $saveActivity->handle($enrollment, $request->validated(), $activity);

        return $this->backToEnrollment($enrollment, __('Atividade atualizada.'));
    }

    public function destroy(Enrollment $enrollment, Activity $activity): RedirectResponse
    {
        Gate::authorize('update', $enrollment);

        $activity->delete();

        return $this->backToEnrollment($enrollment, __('Atividade removida.'));
    }

    private function backToEnrollment(Enrollment $enrollment, string $message): RedirectResponse
    {
        return redirect()->to(route('enrollments.show', $enrollment).'#atividades')->with('flash', $message);
    }
}
