<?php

namespace Tests\Unit\Support\WebPush;

use App\Support\WebPush\P256;
use App\Support\WebPush\PayloadEncrypter;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Concerns\DecryptsWebPush;

class PayloadEncrypterTest extends TestCase
{
    use DecryptsWebPush;

    public function test_subscriber_can_decrypt_the_payload(): void
    {
        $browserKeys = P256::generateKeyPair();
        $authSecret = random_bytes(16);

        $body = PayloadEncrypter::encrypt('{"title":"Olá"}', $browserKeys['public'], $authSecret);

        $this->assertSame(4096, unpack('N', substr($body, 16, 4))[1]);
        $this->assertSame('{"title":"Olá"}', $this->decryptWebPush($body, $browserKeys, $authSecret));
    }

    public function test_another_browser_cannot_decrypt_the_payload(): void
    {
        $recipientKeys = P256::generateKeyPair();
        $authSecret = random_bytes(16);

        $body = PayloadEncrypter::encrypt('segredo', $recipientKeys['public'], $authSecret);

        $this->assertFalse($this->decryptWebPush($body, P256::generateKeyPair(), $authSecret));
    }

    public function test_rejects_payload_larger_than_one_record(): void
    {
        $this->expectException(RuntimeException::class);

        PayloadEncrypter::encrypt(str_repeat('a', 4000), P256::generateKeyPair()['public'], random_bytes(16));
    }
}
