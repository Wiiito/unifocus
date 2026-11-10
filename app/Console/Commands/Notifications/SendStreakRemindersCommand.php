<?php

namespace App\Console\Commands\Notifications;

use App\Models\UserStreak;
use App\Notifications\StreakAtRisk;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

/**
 * Agendado à noite (routes/console.php). Foguinho em risco = último dia
 * cumprido foi ontem: aceso agora, mas apaga à meia-noite se hoje não for
 * cumprido. Quem já cumpriu hoje tem last_completed_on = hoje e fica de fora.
 */
#[Signature('notifications:streaks')]
#[Description('Lembra quem tem foguinho aceso e ainda não cumpriu os desafios de hoje')]
class SendStreakRemindersCommand extends Command
{
    public function handle(): int
    {
        $notified = 0;

        UserStreak::query()
            ->where('current_count', '>', 0)
            ->whereDate('last_completed_on', today()->subDay())
            ->with('user')
            ->chunkById(100, function (Collection $streaks) use (&$notified): void {
                foreach ($streaks as $streak) {
                    $streak->user->notify(new StreakAtRisk($streak->current_count));
                    $notified++;
                }
            });

        $this->components->info("Lembretes de foguinho enviados para {$notified} estudante(s).");

        return self::SUCCESS;
    }
}
