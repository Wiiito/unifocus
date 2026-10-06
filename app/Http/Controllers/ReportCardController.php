<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    /**
     * Boletim: todas as matrículas, agrupadas por período (mais recente
     * primeiro). Usa os caches da matrícula, sem recalcular nada.
     */
    public function __invoke(Request $request): View
    {
        $terms = $request->user()->enrollments()
            ->withSummary()
            ->get()
            ->sortByDesc(fn (Enrollment $enrollment) => $enrollment->classGroup->academicTerm?->starts_on?->getTimestamp() ?? PHP_INT_MAX)
            ->groupBy(fn (Enrollment $enrollment) => $enrollment->classGroup->academicTerm?->name ?? __('Sem período'));

        return view('report-card.index', ['terms' => $terms]);
    }
}
