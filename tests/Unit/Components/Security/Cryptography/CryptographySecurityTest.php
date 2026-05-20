<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Security\Cryptography;

use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\AesEncrypter;
use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\EncryptionKey;
use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\EncryptedPayload;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CryptographySecurityTest extends TestCase
{
    public function test_decrypt_with_wrong_encrypter_key_does_not_leak_data() : void
    {
        $keyMaterial    = str_repeat('a', 32);
        $wrongMaterial  = str_repeat('b', 32);
        $encrypter      = new AesEncrypter($keyMaterial);
        $wrongEncrypter = new AesEncrypter($wrongMaterial);

        $encrypted = $encrypter->encrypt('sensitive-payload', new EncryptionKey($keyMaterial));

        $this->expectException(RuntimeException::class);

        $wrongEncrypter->decrypt($encrypted, new EncryptionKey($wrongMaterial));
    }

    public function test_decrypt_empty_ciphertext_throws() : void
    {
        $keyMaterial   = str_repeat('a', 32);
        $encryptionKey = new EncryptionKey($keyMaterial);
        $encrypter     = new AesEncrypter($keyMaterial);

        $encrypted = $encrypter->encrypt('data', $encryptionKey);

        $empty = new EncryptedPayload(
            cipherText: '',
            iv        : $encrypted->iv(),
            tag       : $encrypted->tag(),
        );

        $this->expectException(RuntimeException::class);

        $encrypter->decrypt($empty, $encryptionKey);
    }

    public function test_reused_iv_produces_different_ciphertext() : void
    {
        $keyMaterial   = str_repeat('a', 32);
        $encryptionKey = new EncryptionKey($keyMaterial);
        $encrypter     = new AesEncrypter($keyMaterial);

        $encrypted1 = $encrypter->encrypt('same-data', $encryptionKey);
        $encrypted2 = $encrypter->encrypt('same-data', $encryptionKey);

        $this->assertNotSame($encrypted1->cipherText(), $encrypted2->cipherText());
        $this->assertNotSame($encrypted1->iv(), $encrypted2->iv());
    }

    public function test_aes_encrypter_rejects_short_key() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('must be 32 bytes');

        new AesEncrypter('short');
    }
}
