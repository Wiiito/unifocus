<?php

namespace App\Notifications;

use App\Notifications\Messages\WebPushMessage;

/**
 * Lembrete noturno para quem tem foguinho aceso e ainda não cumpriu os
 * desafios do dia (comando notifications:streaks).
 */
class StreakAtRisk extends PushNotification
{
    public function __construct(public int $streakDays) {}

    protected function message(object $notifiable): WebPushMessage
    {
        return new WebPushMessage(
            title: __('Seu foguinho está em risco'),
            body: trans_choice(
                '{1} Sua sequência de :count dia apaga à meia-noite. Conclua os desafios de hoje!|[2,*] Sua sequência de :count dias apaga à meia-noite. Conclua os desafios de hoje!',
                $this->streakDays,
            ),
            url: route('dashboard').'#desafios',
            symbol: 'local_fire_department',
            tag: 'streak',
        );
    }
}
