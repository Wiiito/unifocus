<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notificações push (Web Push + VAPID)
    |--------------------------------------------------------------------------
    |
    | Sem as chaves VAPID o push fica desligado: as notificações continuam no
    | sino do cabeçalho e o botão "Ativar notificações" não aparece. Gere um
    | par com `php artisan webpush:vapid`. Trocar as chaves invalida todas as
    | inscrições existentes (os navegadores precisam se inscrever de novo).
    |
    */

    'vapid' => [
        /** Contato do responsável pelo servidor, exigido pelos serviços de push (mailto: ou https:). */
        'subject' => env('VAPID_SUBJECT', 'mailto:'.env('MAIL_FROM_ADDRESS', 'nao-responda@unifocus.test')),

        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

    /** Por quanto tempo (segundos) o serviço de push guarda a notificação se o aparelho estiver offline. */
    'ttl' => (int) env('WEBPUSH_TTL', 86400),

];
