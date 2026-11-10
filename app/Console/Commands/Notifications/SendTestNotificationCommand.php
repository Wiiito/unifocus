<?php

namespace App\Console\Commands\Notifications;

use App\Models\Message;
use App\Models\User;
use App\Notifications\DeadlinesApproaching;
use App\Notifications\FriendRequestReceived;
use App\Notifications\NewDirectMessage;
use App\Notifications\PushNotification;
use App\Notifications\StreakAtRisk;
use App\Notifications\TestNotification;
use App\Support\Students\AgendaItem;
use App\Support\WebPush\WebPushSender;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Envia uma notificação de exemplo a um estudante, para testar o sino e o
 * push sem esperar os eventos reais ou os horários agendados. Os dados de
 * exemplo não são gravados (exceto a própria notificação no sino).
 */
#[Signature('notifications:test {user : ID ou e-mail do estudante} {--type=teste : teste, amizade, mensagem, prazo ou foguinho} {--now : Entrega o push agora, sem passar pela fila}')]
#[Description('Envia uma notificação de teste (sino + push) para um estudante')]
class SendTestNotificationCommand extends Command
{
    private const TYPES = ['teste', 'amizade', 'mensagem', 'prazo', 'foguinho'];

    public function handle(WebPushSender $sender): int
    {
        $identifier = (string) $this->argument('user');
        $user = User::query()->where(is_numeric($identifier) ? 'id' : 'email', $identifier)->first();

        if ($user === null) {
            $this->components->error('Estudante não encontrado.');

            return self::FAILURE;
        }

        $type = (string) $this->option('type');

        if (! in_array($type, self::TYPES, true)) {
            $this->components->error('Tipo inválido. Use: '.implode(', ', self::TYPES).'.');

            return self::FAILURE;
        }

        if ($this->option('now')) {
            config(['queue.default' => 'sync']);
        }

        $user->notify($this->notificationOfType($type, $user));

        $this->components->info("Notificação \"{$type}\" enviada para {$user->name} <{$user->email}>.");
        $this->reportPushStatus($user, $sender);

        return self::SUCCESS;
    }

    private function notificationOfType(string $type, User $user): PushNotification
    {
        $sampleFriend = $user->friends()->first() ?? $user;

        return match ($type) {
            'amizade' => new FriendRequestReceived(new User(['name' => 'Estudante de teste'])),
            'mensagem' => new NewDirectMessage(new Message(['body' => 'Mensagem de teste: bora estudar hoje?']), $sampleFriend),
            'prazo' => new DeadlinesApproaching(collect([
                new AgendaItem(AgendaItem::KIND_DEADLINE, 'Prova de teste', 'Matéria de exemplo · Prova', now()->addHours(5), 'quiz'),
            ])),
            'foguinho' => new StreakAtRisk(3),
            default => new TestNotification,
        };
    }

    private function reportPushStatus(User $user, WebPushSender $sender): void
    {
        $subscriptions = $user->pushSubscriptions()->count();

        if (! $sender->isConfigured()) {
            $this->components->warn('Push desligado: sem chaves VAPID (php artisan webpush:vapid). Só o sino recebeu.');

            return;
        }

        if ($subscriptions === 0) {
            $this->components->warn('Nenhum navegador inscrito: ative as notificações pelo sino. Só o sino recebeu.');

            return;
        }

        $this->components->info($this->option('now')
            ? "Push entregue a {$subscriptions} navegador(es)."
            : "Push enfileirado para {$subscriptions} navegador(es); a fila (queue:work) faz a entrega.");
    }
}
