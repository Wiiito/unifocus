<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Notificações do sino. Abrir uma notificação (pelo sino ou pelo push) a
 * marca como lida e leva ao destino gravado nela.
 */
class NotificationController extends Controller
{
    public function show(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()->notifications()->findOrFail($notification);

        $record->markAsRead();

        return redirect()->to($record->data['url'] ?? route('dashboard'));
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
