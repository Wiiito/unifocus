<?php

namespace App\Support\WebPush;

use RuntimeException;

/**
 * Cifra o conteúdo da notificação para um navegador (RFC 8291, codificação
 * aes128gcm do RFC 8188). Só o navegador inscrito, dono da chave privada
 * correspondente a public_key, consegue ler; o serviço de push apenas
 * repassa bytes opacos.
 */
final class PayloadEncrypter
{
    /** Tamanho de registro anunciado; a mensagem inteira cabe em um registro. */
    private const RECORD_SIZE = 4096;

    /**
     * @param  string  $userAgentPublicKey  Chave p256dh do navegador (65 bytes).
     * @param  string  $authSecret  Segredo "auth" do navegador (16 bytes).
     */
    public static function encrypt(string $payload, string $userAgentPublicKey, string $authSecret): string
    {
        if (strlen($payload) > self::RECORD_SIZE - 103) {
            throw new RuntimeException('Conteúdo grande demais para uma notificação push.');
        }

        $ephemeral = P256::generateKeyPair();

        $sharedSecret = openssl_pkey_derive(P256::publicKeyFromRaw($userAgentPublicKey), $ephemeral['key'], 32)
            ?: throw new RuntimeException('Falha no ECDH com a chave do navegador.');

        $inputKey = hash_hkdf('sha256', $sharedSecret, 32, "WebPush: info\x00".$userAgentPublicKey.$ephemeral['public'], $authSecret);

        $salt = random_bytes(16);
        $contentKey = hash_hkdf('sha256', $inputKey, 16, "Content-Encoding: aes128gcm\x00", $salt);
        $nonce = hash_hkdf('sha256', $inputKey, 12, "Content-Encoding: nonce\x00", $salt);

        /** \x02 = delimitador do último (e único) registro, sem padding. */
        $ciphertext = openssl_encrypt($payload."\x02", 'aes-128-gcm', $contentKey, OPENSSL_RAW_DATA, $nonce, $tag);

        return $salt.pack('N', self::RECORD_SIZE).chr(65).$ephemeral['public'].$ciphertext.$tag;
    }
}
