<?php

namespace App\Console\Commands\WebPush;

use App\Support\WebPush\VapidKeys;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Gera o par VAPID e mostra as linhas para o .env. Não grava nada: trocar
 * as chaves invalida as inscrições existentes, então é uma decisão manual.
 */
#[Signature('webpush:vapid')]
#[Description('Gera um par de chaves VAPID para as notificações push')]
class GenerateVapidKeysCommand extends Command
{
    public function handle(): int
    {
        $keys = VapidKeys::generate(config('webpush.vapid.subject'));

        $this->components->info('Adicione ao .env (trocar as chaves exige que os navegadores se inscrevam de novo):');
        $this->line("VAPID_PUBLIC_KEY={$keys->encodedPublicKey()}");
        $this->line("VAPID_PRIVATE_KEY={$keys->encodedPrivateKey()}");

        return self::SUCCESS;
    }
}
