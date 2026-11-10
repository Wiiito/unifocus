<?php

namespace Tests\Concerns;

use App\Support\WebPush\P256;

/**
 * Faz o papel do navegador: decifra um corpo aes128gcm (RFC 8291) com a
 * chave privada da inscrição. Prova que o conteúdo enviado é legível pelo
 * destinatário, e só por ele.
 */
trait DecryptsWebPush
{
    /**
     * @param  array{public: string, private: string}  $userAgentKeys
     */
    protected function decryptWebPush(string $body, array $userAgentKeys, string $authSecret): string|false
    {
        $salt = substr($body, 0, 16);
        $idLength = ord($body[20]);
        $serverPublicKey = substr($body, 21, $idLength);
        $record = substr($body, 21 + $idLength);

        $sharedSecret = openssl_pkey_derive(
            P256::publicKeyFromRaw($serverPublicKey),
            P256::privateKeyFromRaw($userAgentKeys['private'], $userAgentKeys['public']),
            32,
        );

        $inputKey = hash_hkdf('sha256', $sharedSecret, 32, "WebPush: info\x00".$userAgentKeys['public'].$serverPublicKey, $authSecret);
        $contentKey = hash_hkdf('sha256', $inputKey, 16, "Content-Encoding: aes128gcm\x00", $salt);
        $nonce = hash_hkdf('sha256', $inputKey, 12, "Content-Encoding: nonce\x00", $salt);

        $plaintext = openssl_decrypt(substr($record, 0, -16), 'aes-128-gcm', $contentKey, OPENSSL_RAW_DATA, $nonce, substr($record, -16));

        return $plaintext === false ? false : rtrim($plaintext, "\x00\x02");
    }
}
