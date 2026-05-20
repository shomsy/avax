<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Security\Cryptography;

use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\AesEncrypter;
use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\EncryptionKey;
use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\EncryptedPayload;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CryptographyTest extends TestCase
{
    public function test_aes_encrypter_roundtrip() : void
    {
        $keyMaterial   = str_repeat('a', 32);
        $encryptionKey = new EncryptionKey($keyMaterial);
        $encrypter     = new AesEncrypter($keyMaterial);

        $encrypted = $encrypter->encrypt('secret-data', $encryptionKey);
        $decrypted = $encrypter->decrypt($encrypted, $encryptionKey);

        $this->assertSame('secret-data', $decrypted);
    }

    public function test_decrypt_with_wrong_key_throws() : void
    {
        $keyMaterial    = str_repeat('a', 32);
        $wrongMaterial  = str_repeat('b', 32);
        $encryptionKey  = new EncryptionKey($keyMaterial);
        $encrypter      = new AesEncrypter($keyMaterial);
        $wrongEncrypter = new AesEncrypter($wrongMaterial);

        $encrypted = $encrypter->encrypt('secret-data', $encryptionKey);

        $this->expectException(RuntimeException::class);

        $wrongEncrypter->decrypt($encrypted, new EncryptionKey($wrongMaterial));
    }

    public function test_decrypt_tampered_ciphertext_throws() : void
    {
        $keyMaterial   = str_repeat('a', 32);
        $encryptionKey = new EncryptionKey($keyMaterial);
        $encrypter     = new AesEncrypter($keyMaterial);

        $encrypted = $encrypter->encrypt('secret-data', $encryptionKey);

        $ct = $encrypted->cipherText();
        $ct[0] = ~$ct[0];

        $tampered = new EncryptedPayload(
            cipherText: $ct,
            iv        : $encrypted->iv(),
            tag       : $encrypted->tag(),
        );

        $this->expectException(RuntimeException::class);

        $encrypter->decrypt($tampered, $encryptionKey);
    }

    public function test_decrypt_tampered_tag_throws() : void
    {
        $keyMaterial   = str_repeat('a', 32);
        $encryptionKey = new EncryptionKey($keyMaterial);
        $encrypter     = new AesEncrypter($keyMaterial);

        $encrypted = $encrypter->encrypt('secret-data', $encryptionKey);

        $tampered = new EncryptedPayload(
            cipherText: $encrypted->cipherText(),
            iv        : $encrypted->iv(),
            tag       : str_repeat("\x00", 16),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Decryption failed');

        $encrypter->decrypt($tampered, $encryptionKey);
    }

    public function test_encryption_key_rejects_wrong_length() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be exactly 32 bytes');

        new EncryptionKey('too-short');
    }

    public function test_encryption_key_from_base64() : void
    {
        $key = EncryptionKey::fromBase64(base64_encode(str_repeat('c', 32)));

        $this->assertTrue($key->isValid());
        $this->assertSame(str_repeat('c', 32), $key->raw());
    }

    public function test_encryption_key_from_invalid_base64_throws() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid base64');

        EncryptionKey::fromBase64('!!!not-base64!!!');
    }

    public function test_encryption_key_roundtrip_to_base64() : void
    {
        $original = new EncryptionKey(str_repeat('d', 32), '2');
        $b64 = $original->toBase64();
        $restored = EncryptionKey::fromBase64($b64, '2');

        $this->assertSame($original->raw(), $restored->raw());
        $this->assertSame($original->version(), $restored->version());
    }

    public function test_aes_encrypter_roundtrip_array_value() : void
    {
        $keyMaterial   = str_repeat('e', 32);
        $encryptionKey = new EncryptionKey($keyMaterial);
        $encrypter     = new AesEncrypter($keyMaterial);

        $data = ['user_id' => 42, 'role' => 'admin'];
        $encrypted = $encrypter->encrypt($data, $encryptionKey);
        $decrypted = $encrypter->decrypt($encrypted, $encryptionKey);

        $this->assertSame(json_encode($data), $decrypted);
    }
}
