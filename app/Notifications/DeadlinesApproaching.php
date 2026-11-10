<?php

namespace App\Notifications;

use App\Notifications\Messages\WebPushMessage;
use App\Support\Students\AgendaItem;
use Illuminate\Support\Collection;

/**
 * Lembrete diário dos prazos de atividades não entregues que vencem nas
 * próximas 24 horas (comando notifications:deadlines).
 */
class DeadlinesApproaching extends PushNotification
{
    /**
     * @param  Collection<int, AgendaItem>  $deadlines
     */
    public function __construct(public Collection $deadlines) {}

    protected function message(object $notifiable): WebPushMessage
    {
        /** @var AgendaItem $first */
        $first = $this->deadlines->first();

        if ($this->deadlines->count() === 1) {
            return new WebPushMessage(
                title: __('Prazo chegando: :title', ['title' => $first->title]),
                body: __(':context · vence :when.', ['context' => $first->context, 'when' => $this->describeDueDate($first)]),
                url: $first->url ?? route('agenda'),
                symbol: 'event_upcoming',
                tag: 'deadlines',
            );
        }

        return new WebPushMessage(
            title: __(':count prazos nas próximas 24 horas', ['count' => $this->deadlines->count()]),
            body: $this->deadlines
                ->map(fn (AgendaItem $deadline): string => $deadline->title.' ('.$this->describeDueDate($deadline).')')
                ->join(', ', __(' e ')),
            url: route('agenda'),
            symbol: 'event_upcoming',
            tag: 'deadlines',
        );
    }

    private function describeDueDate(AgendaItem $deadline): string
    {
        return __($deadline->startsAt->isToday() ? 'hoje às :time' : 'amanhã às :time', [
            'time' => $deadline->startsAt->format('H:i'),
        ]);
    }
}
