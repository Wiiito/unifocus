<?php

namespace App\Support\WebPush;

use OpenSSLAsymmetricKey;
use RuntimeException;

/**
 * Utilitários da curva P-256 (prime256v1) usada pelo Web Push. O navegador e
 * o VAPID trocam chaves "cruas" (ponto público de 65 bytes, escalar privado
 * de 32 bytes) em base64url; o OpenSSL só entende PEM/DER. Esta classe faz a
 * ponte entre os dois formatos.
 */
final class P256
{
    /** Cabeçalho DER de uma SubjectPublicKeyInfo EC P-256 (falta só o ponto). */
    private const PUBLIC_KEY_DER_PREFIX = '3059301306072a8648ce3d020106082a8648ce3d030107034200';

    /**
     * @return array{public: string, private: string, key: OpenSSLAsymmetricKey}
     */
    public static function generateKeyPair(): array
    {
        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]) ?: throw new RuntimeException('Não foi possível gerar a chave EC P-256: '.openssl_error_string());

        $details = openssl_pkey_get_details($key)['ec'];

        return [
            'public' => "\x04".self::pad($details['x']).self::pad($details['y']),
            'private' => self::pad($details['d']),
            'key' => $key,
        ];
    }

    /**
     * Chave privada a partir do escalar (32 bytes) e do ponto público (65 bytes),
     * montando a estrutura ECPrivateKey (SEC 1) que o OpenSSL importa.
     */
    public static function privateKeyFromRaw(string $private, string $public): OpenSSLAsymmetricKey
    {
        $der = hex2bin('307702010104').chr(32).$private
            .hex2bin('a00a06082a8648ce3d030107a144034200').$public;

        return openssl_pkey_get_private(self::pem($der, 'EC PRIVATE KEY'))
            ?: throw new RuntimeException('Chave privada P-256 inválida.');
    }

    public static function publicKeyFromRaw(string $public): OpenSSLAsymmetricKey
    {
        if (strlen($public) !== 65 || $public[0] !== "\x04") {
            throw new RuntimeException('Chave pública P-256 inválida.');
        }

        return openssl_pkey_get_public(self::pem(hex2bin(self::PUBLIC_KEY_DER_PREFIX).$public, 'PUBLIC KEY'))
            ?: throw new RuntimeException('Chave pública P-256 inválida.');
    }

    /**
     * O OpenSSL assina ECDSA em DER (SEQUENCE de dois INTEGER); o JWT ES256
     * espera r||s com 32 bytes cada.
     */
    public static function derSignatureToRaw(string $der): string
    {
        $offset = 2;
        $parts = '';

        foreach (['r', 's'] as $ignored) {
            $length = ord($der[$offset + 1]);
            $integer = substr($der, $offset + 2, $length);
            $parts .= self::pad(ltrim($integer, "\x00"));
            $offset += 2 + $length;
        }

        return $parts;
    }

    public static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/'), true);
    }

    private static function pad(string $value): string
    {
        return str_pad($value, 32, "\x00", STR_PAD_LEFT);
    }

    private static function pem(string $der, string $label): string
    {
        return "-----BEGIN {$label}-----\n".chunk_split(base64_encode($der), 64, "\n")."-----END {$label}-----\n";
    }
}
