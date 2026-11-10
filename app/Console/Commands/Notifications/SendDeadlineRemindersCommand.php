<?php

namespace App\Console\Commands\Notifications;

use App\Models\User;
use App\Notifications\DeadlinesApproaching;
use App\Support\Students\AgendaItem;
use App\Support\Students\StudentAgenda;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Agendado uma vez por dia (routes/console.php): como a janela é de 24h,
 * cada prazo é lembrado exatamente uma vez.
 */
#[Signature('notifications:deadlines')]
#[Description('Avisa os estudantes sobre atividades não entregues que vencem nas próximas 24 horas')]
class SendDeadlineRemindersCommand extends Command
{
    public function handle(): int
    {
        $from = now();
        $until = now()->addDay();
        $notified = 0;

        User::query()
            ->whereHas('enrollments', fn (Builder $query) => $query->ongoing())
            ->chunkById(100, function (Collection $users) use ($from, $until, &$notified): void {
                foreach ($users as $user) {
                    /** Instância nova por estudante: a agenda memoriza as matrículas de quem consulta. */
                    $deadlines = app(StudentAgenda::class)
                        ->deadlines($user, $from, $until)
                        ->sortBy(fn (AgendaItem $deadline): int => $deadline->startsAt->getTimestamp())
                        ->values();

                    if ($deadlines->isNotEmpty()) {
                        $user->notify(new DeadlinesApproaching($deadlines));
                        $notified++;
                    }
                }
            });

        $this->components->info("Lembretes de prazo enviados para {$notified} estudante(s).");

        return self::SUCCESS;
    }
}
