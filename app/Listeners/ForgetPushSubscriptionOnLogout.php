<?php

namespace App\Listeners;

use App\Http\Controllers\PushSubscriptionController;
use App\Models\PushSubscription;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

/**
 * Quem sai da conta não deve continuar recebendo notificações dela naquele
 * navegador (computador compartilhado, por exemplo). Ao entrar de novo com
 * a permissão já concedida, o front inscreve o navegador outra vez.
 */
class ForgetPushSubscriptionOnLogout
{
    public function __construct(private Request $request) {}

    public function handle(Logout $event): void
    {
        if ($event->user === null || ! $this->request->hasSession()) {
            return;
        }

        $endpoint = $this->request->session()->pull(PushSubscriptionController::SESSION_KEY);

        if ($endpoint !== null) {
            PushSubscription::query()
                ->where('user_id', $event->user->getAuthIdentifier())
                ->where('endpoint', $endpoint)
                ->delete();
        }
    }
}
