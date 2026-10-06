<?php

namespace App\Http\Controllers;

use App\Actions\Enrollments\EnrollInSubject;
use App\Actions\Enrollments\UpdateEnrollment;
use App\Enums\EnrollmentStatus;
use App\Http\Requests\Enrollments\StoreEnrollmentRequest;
use App\Http\Requests\Enrollments\UpdateEnrollmentRequest;
use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\User;
use App\Support\Academic\EnrollmentStandingCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EnrollmentController extends Controller
{
    /**
     * Matérias do estudante: as em andamento primeiro, depois o histórico.
     */
    public function index(Request $request): View
    {
        $enrollments = $request->user()->enrollments()
            ->withSummary()
            ->latest('enrolled_at')
            ->get()
            ->partition(fn (Enrollment $enrollment) => $enrollment->status === EnrollmentStatus::Active);

        return view('enrollments.index', [
            'ongoing' => $enrollments[0],
            'history' => $enrollments[1],
        ]);
    }

    public function create(Request $request): View
    {
        return view('enrollments.create', [
            ...$this->formOptions($request->user()),
            'enrollment' => null,
        ]);
    }

    public function store(StoreEnrollmentRequest $request, EnrollInSubject $enrollInSubject): RedirectResponse
    {
        $enrollment = $enrollInSubject->handle($request->user(), $request->validated());

        return redirect()->route('enrollments.show', $enrollment)
            ->with('flash', __('Matrícula criada. Agora é só registrar aulas, atividades e notas.'));
    }

    public function show(Enrollment $enrollment, EnrollmentStandingCalculator $calculator): View
    {
        Gate::authorize('view', $enrollment);

        $enrollment->load([
            'classGroup.subject.institution',
            'classGroup.academicTerm.institution',
            'classGroup.academicTerm.events',
        ]);

        return view('enrollments.show', [
            'enrollment' => $enrollment,
            'standing' => $calculator->calculate($enrollment),
            'activities' => $enrollment->activities()
                ->with([
                    'submissions' => fn ($query) => $query->whereBelongsTo($enrollment),
                    'gradeEntries' => fn ($query) => $query->whereBelongsTo($enrollment),
                ])
                ->orderByRaw('due_at IS NULL')
                ->orderBy('due_at')
                ->get(),
            'lessons' => $enrollment->lessons()
                ->with(['attendances' => fn ($query) => $query->whereBelongsTo($enrollment)])
                ->latest('starts_at')
                ->get(),
            'manualGrades' => $enrollment->gradeEntries()->whereNull('activity_id')->latest('recorded_at')->get(),
        ]);
    }

    public function edit(Enrollment $enrollment): View
    {
        Gate::authorize('update', $enrollment);

        return view('enrollments.edit', [
            ...$this->formOptions($enrollment->user),
            'enrollment' => $enrollment->load('classGroup.subject'),
        ]);
    }

    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment, UpdateEnrollment $updateEnrollment): RedirectResponse
    {
        $updateEnrollment->handle($enrollment, $request->validated());

        return redirect()->route('enrollments.show', $enrollment)->with('flash', __('Matrícula atualizada.'));
    }

    /**
     * Remove a matéria da vida do estudante. A turma é pessoal, então vai
     * junto, com aulas, atividades e notas.
     */
    public function destroy(Enrollment $enrollment): RedirectResponse
    {
        Gate::authorize('delete', $enrollment);

        $enrollment->classGroup->forceDelete();

        return redirect()->route('enrollments.index')->with('flash', __('Matéria removida.'));
    }

    /**
     * @return array{subjects: Collection<int, Subject>, terms: Collection<int, AcademicTerm>}
     */
    private function formOptions(User $user): array
    {
        return [
            'subjects' => Subject::query()->availableTo($user)->with('institution')->orderBy('name')->get(),
            'terms' => AcademicTerm::query()
                ->whereIn('institution_id', $user->activeInstitutionIds())
                ->with('institution')
                ->orderByDesc('starts_on')
                ->get(),
        ];
    }
}
