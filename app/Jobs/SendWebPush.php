<?php

namespace App\Jobs;

use App\Models\PushSubscription;
use App\Notifications\Messages\WebPushMessage;
use App\Support\WebPush\WebPushSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Uma entrega push para um navegador. Falhas temporárias do serviço de push
 * (5xx, 429) voltam para a fila; recusas definitivas são só registradas.
 */
class SendWebPush implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60];

    /** A inscrição pode ter sido apagada (logout, expiração) antes do job rodar. */
    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public PushSubscription $subscription,
        public WebPushMessage $message,
    ) {}

    public function handle(WebPushSender $sender): void
    {
        $response = $sender->send($this->subscription, $this->message);

        if ($response->serverError() || $response->status() === 429) {
            $response->throw();
        }

        if ($response->clientError() && ! in_array($response->status(), [404, 410], true)) {
            Log::warning('Web Push recusado pelo serviço de push.', [
                'subscription_id' => $this->subscription->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}
