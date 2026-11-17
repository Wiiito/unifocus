<?php

namespace App\Http\Controllers;

use App\Support\Streaks\StreakService;
use App\Support\Students\StudentAgenda;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    /** Janela exibida na agenda. */
    private const DAYS_AHEAD = 30;

    public function __invoke(Request $request, StudentAgenda $agenda, StreakService $streaks): View
    {
        $user = $request->user();

        $streaks->recordAgendaView($user);

        return view('agenda.index', [
            'calendar' => $agenda->calendarData($user),
            'daysAhead' => self::DAYS_AHEAD,
        ]);
    }
}
