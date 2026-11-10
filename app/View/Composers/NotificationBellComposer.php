<?php

namespace App\View\Composers;

use App\Http\Controllers\PushSubscriptionController;
use App\Support\WebPush\VapidKeys;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dados do sino do cabeçalho: contagem de não lidas, últimas notificações e
 * a configuração do push (chave pública VAPID e o endpoint que este
 * navegador já registrou nesta sessão, para o front saber se precisa sincronizar).
 */
class NotificationBellComposer
{
    /** Quantas notificações o sino lista. */
    private const RECENT_LIMIT = 8;

    public function __construct(private Request $request) {}

    public function compose(View $view): void
    {
        $user = $this->request->user();

        if ($user === null) {
            return;
        }

        $view->with([
            'unreadNotificationCount' => $user->unreadNotifications()->count(),
            'recentNotifications' => $user->notifications()->latest()->limit(self::RECENT_LIMIT)->get(),
            'pushConfig' => [
                'publicKey' => VapidKeys::fromConfig()?->encodedPublicKey(),
                'subscribeUrl' => route('push-subscriptions.store'),
                'syncedEndpoint' => $this->request->session()->get(PushSubscriptionController::SESSION_KEY),
            ],
        ]);
    }
}
