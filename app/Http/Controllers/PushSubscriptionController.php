<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePushSubscriptionRequest;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Liga/desliga o push no navegador atual. O endpoint inscrito fica na
 * sessão: assim o front sabe se já está sincronizado e o logout consegue
 * desinscrever exatamente este navegador.
 */
class PushSubscriptionController extends Controller
{
    public const SESSION_KEY = 'push_subscription_endpoint';

    /**
     * Cria ou atualiza pelo endpoint. Se o navegador estava com outra conta,
     * a inscrição passa para quem está logado agora.
     */
    public function store(StorePushSubscriptionRequest $request): Response
    {
        $subscription = PushSubscription::updateOrCreate(
            ['endpoint' => $request->validated('endpoint')],
            [
                'user_id' => $request->user()->id,
                'public_key' => $request->validated('keys.p256dh'),
                'auth_token' => $request->validated('keys.auth'),
            ],
        );

        $request->session()->put(self::SESSION_KEY, $subscription->endpoint);

        return response()->noContent();
    }

    public function destroy(Request $request): Response
    {
        $endpoint = $request->validate(['endpoint' => ['required', 'string', 'max:1000']])['endpoint'];

        $request->user()->pushSubscriptions()->where('endpoint', $endpoint)->delete();
        $request->session()->forget(self::SESSION_KEY);

        return response()->noContent();
    }
}
