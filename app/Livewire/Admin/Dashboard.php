<?php

namespace App\Livewire\Admin;

use App\Support\Admin\PlatformStatistics;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::admin')]
#[Title('Visão geral')]
class Dashboard extends Component
{
    /** Janela, em dias, das métricas "recentes". */
    public const PERIOD_DAYS = 30;

    public function render(PlatformStatistics $statistics): View
    {
        return view('livewire.admin.dashboard', [
            'periodDays' => self::PERIOD_DAYS,
            'overview' => $statistics->overview(self::PERIOD_DAYS),
            'engagement' => $statistics->engagement(self::PERIOD_DAYS),
            'questionBank' => $statistics->questionBank(self::PERIOD_DAYS),
            'signups' => $statistics->signupsPerDay(self::PERIOD_DAYS),
            'topSubjects' => $statistics->topSubjects(),
            'outcomes' => $statistics->outcomes(),
            'recentActivity' => $statistics->recentActivity(),
        ]);
    }
}
