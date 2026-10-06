<?php

namespace App\Http\Controllers;

use App\Support\Students\PracticeQuestionPicker;
use App\Support\Students\StudentAgenda;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Quantos dias à frente o card "Próximas" mostra. */
    private const UPCOMING_DAYS = 14;

    /**
     * Resumo da vida acadêmica: matérias em andamento, próximos compromissos e
     * o baralho de revisão do banco de questões.
     */
    public function __invoke(Request $request, StudentAgenda $agenda, PracticeQuestionPicker $picker): View
    {
        $user = $request->user();

        return view('dashboard', [
            'enrollments' => $user->enrollments()->ongoing()->withSummary()->get(),
            'upcoming' => $agenda->overdue($user)
                ->concat($agenda->between($user, now(), now()->addDays(self::UPCOMING_DAYS)))
                ->take(6),
            'deck' => $picker->deck($user),
        ]);
    }
}
