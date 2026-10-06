<?php

namespace App\Http\Controllers\Enrollments;

use App\Enums\GradeKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\Enrollments\GradeEntryRequest;
use App\Models\Enrollment;
use App\Models\GradeEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Notas avulsas (participação, prova sem atividade cadastrada...). A nota de
 * uma atividade é editada no formulário da atividade, então aqui ela "não
 * existe" (404).
 */
class GradeEntryController extends Controller
{
    public function create(Enrollment $enrollment): View
    {
        Gate::authorize('update', $enrollment);

        return view('enrollments.grade-entries.form', [
            'enrollment' => $enrollment,
            'gradeEntry' => new GradeEntry(['kind' => GradeKind::Exam]),
        ]);
    }

    public function store(GradeEntryRequest $request, Enrollment $enrollment): RedirectResponse
    {
        $enrollment->gradeEntries()->create([
            ...$request->validated(),
            'recorded_by_user_id' => $request->user()->id,
            'recorded_at' => now(),
        ]);

        return $this->backToEnrollment($enrollment, __('Nota lançada.'));
    }

    public function edit(Enrollment $enrollment, GradeEntry $gradeEntry): View
    {
        Gate::authorize('update', $enrollment);
        $this->ensureIsStandalone($gradeEntry);

        return view('enrollments.grade-entries.form', [
            'enrollment' => $enrollment,
            'gradeEntry' => $gradeEntry,
        ]);
    }

    public function update(GradeEntryRequest $request, Enrollment $enrollment, GradeEntry $gradeEntry): RedirectResponse
    {
        $this->ensureIsStandalone($gradeEntry);

        $gradeEntry->update([
            ...$request->validated(),
            'recorded_by_user_id' => $request->user()->id,
            'recorded_at' => now(),
        ]);

        return $this->backToEnrollment($enrollment, __('Nota atualizada.'));
    }

    public function destroy(Enrollment $enrollment, GradeEntry $gradeEntry): RedirectResponse
    {
        Gate::authorize('update', $enrollment);
        $this->ensureIsStandalone($gradeEntry);

        $gradeEntry->delete();

        return $this->backToEnrollment($enrollment, __('Nota removida.'));
    }

    private function ensureIsStandalone(GradeEntry $gradeEntry): void
    {
        abort_if($gradeEntry->activity_id !== null, 404);
    }

    private function backToEnrollment(Enrollment $enrollment, string $message): RedirectResponse
    {
        return redirect()->to(route('enrollments.show', $enrollment).'#notas')->with('flash', $message);
    }
}
