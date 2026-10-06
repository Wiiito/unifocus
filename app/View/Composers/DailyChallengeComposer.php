<?php

namespace App\View\Composers;

use App\Support\Streaks\StreakService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Entrega o quadro do dia ($dailyBoard) do estudante logado às views que
 * mostram o foguinho e a meta diária, sem consulta dentro do Blade.
 */
class DailyChallengeComposer
{
    public function __construct(private StreakService $streaks, private Request $request) {}

    public function compose(View $view): void
    {
        $user = $this->request->user();

        if ($user !== null) {
            $view->with('dailyBoard', $this->streaks->boardFor($user));
        }
    }
}
