<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/** Lembretes push (horário de config('app.timezone')). */
Schedule::command('notifications:deadlines')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('notifications:streaks')->dailyAt('20:00')->withoutOverlapping();
