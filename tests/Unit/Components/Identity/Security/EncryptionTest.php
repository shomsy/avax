<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Security;

use Avax\Components\Identity\Security\System\Capabilities\Encryption\EncryptedPayload;
use Avax\Components\Identity\Security\System\Capabilities\Encryption\Encrypter;
use Avax\Components\Identity\Security\System\Capabilities\Encryption\EncrypterInterface;
use Avax\Components\Identity\Security\System\Capabilities\Encryption\EncryptionKey;
use Avax\Components\Identity\Security\System\Capabilities\Encryption\KeyResolver;
use Avax\Components\Identity\Security\System\Configuration\EncryptionConfiguration;
use Avax\Components\Identity\Security\System\Flows\DecryptValue\DecryptValue;
use Avax\Components\Identity\Security\System\Flows\EncryptValue\EncryptValue;
use Avax\Components\Identity\Security\System\Foundation\Failure\DecryptionFailed;
use Avax\Components\Identity\Security\System\Foundation\Failure\EncryptionFailed;
use Avax\Components\Identity\Security\System\PublicSurface\Encryption;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Comprehensive tests for the encryption system.
 */
final class EncryptionTest extends TestCase
{
    private EncryptionKey      $key;
    private EncrypterInterface $encrypter;

    #[Test]
    public function key_generate_creates_valid_key() : void
    {
        $key = EncryptionKey::generate('1');

        $this->assertTrue($key->isValid());
        $this->assertEquals('1', $key->version());
    }

    // ==========================================
    // EncryptionKey Tests
    // ==========================================

    #[Test]
    public function key_from_base64_creates_valid_key() : void
    {
        $originalKey = EncryptionKey::generate('test');
        $base64      = $originalKey->toBase64();

        $restoredKey = EncryptionKey::fromBase64($base64, 'test');

        $this->assertTrue($restoredKey->isValid());
        $this->assertEquals($originalKey->toBase64(), $restoredKey->toBase64());
        $this->assertEquals('test', $restoredKey->version());
    }

    #[Test]
    public function key_from_invalid_base64_throws_exception() : void
    {
        $this->expectException(InvalidArgumentException::class);

        EncryptionKey::fromBase64('!!!invalid-base64!!!', '1');
    }

    #[Test]
    public function key_with_wrong_length_throws_exception() : void
    {
        $this->expectException(InvalidArgumentException::class);

        new EncryptionKey('too-short-key', '1');
    }

    #[Test]
    public function key_is_always_valid_after_construction() : void
    {
        $key = EncryptionKey::generate('1');

        $this->assertTrue($key->isValid());
    }

    #[Test]
    public function payload_serialize_and_deserialize_roundtrip() : void
    {
        $cipherText = random_bytes(32);
        $iv         = random_bytes(12);
        $tag        = random_bytes(16);

        $payload    = new EncryptedPayload($cipherText, $iv, $tag, '1');
        $serialized = $payload->serialize();

        $restored = EncryptedPayload::deserialize($serialized);

        $this->assertEquals($cipherText, $restored->cipherText());
        $this->assertEquals($iv, $restored->iv());
        $this->assertEquals($tag, $restored->tag());
        $this->assertEquals('1', $restored->keyVersion());
    }

    // ==========================================
    // EncryptedPayload Tests
    // ==========================================

    #[Test]
    public function payload_deserialize_invalid_format_throws_exception() : void
    {
        $this->expectException(DecryptionFailed::class);

        EncryptedPayload::deserialize('invalid-payload-format');
    }

    #[Test]
    public function payload_deserialize_invalid_version_throws_exception() : void
    {
        $this->expectException(DecryptionFailed::class);

        EncryptedPayload::deserialize('v2:YWJj:ZGVm:Z2hp:MQ==');
    }

    #[Test]
    public function payload_deserialize_invalid_base64_throws_exception() : void
    {
        $this->expectException(DecryptionFailed::class);

        EncryptedPayload::deserialize('v1:!!!invalid!!!:YWJj:ZGVm:MQ==');
    }

    #[Test]
    public function payload_is_immutable() : void
    {
        $cipherText = random_bytes(32);
        $iv         = random_bytes(12);
        $tag        = random_bytes(16);

        $payload = new EncryptedPayload($cipherText, $iv, $tag, '1');

        $this->assertEquals($cipherText, $payload->cipherText());
        $this->assertEquals($iv, $payload->iv());
        $this->assertEquals($tag, $payload->tag());
        $this->assertEquals('1', $payload->keyVersion());
    }

    #[Test]
    public function encrypt_decrypt_string_roundtrip() : void
    {
        $originalValue = 'Hello, World!';

        $encrypted = $this->encrypter->encrypt($originalValue, $this->key);
        $decrypted = $this->encrypter->decrypt($encrypted, $this->key);

        $this->assertEquals($originalValue, $decrypted);
    }

    // ==========================================
    // Encrypter Tests - Roundtrip
    // ==========================================

    #[Test]
    public function encrypt_decrypt_empty_string() : void
    {
        $originalValue = '';

        $encrypted = $this->encrypter->encrypt($originalValue, $this->key);
        $decrypted = $this->encrypter->decrypt($encrypted, $this->key);

        $this->assertEquals($originalValue, $decrypted);
    }

    #[Test]
    public function encrypt_decrypt_special_characters() : void
    {
        $originalValue = 'Special chars: !@#$%^&*()_+-=[]{}|;:\'",.<>?/~`';

        $encrypted = $this->encrypter->encrypt($originalValue, $this->key);
        $decrypted = $this->encrypter->decrypt($encrypted, $this->key);

        $this->assertEquals($originalValue, $decrypted);
    }

    #[Test]
    public function encrypt_decrypt_unicode_characters() : void
    {
        $originalValue = 'Unicode: 你好世界 🌍 éàü';

        $encrypted = $this->encrypter->encrypt($originalValue, $this->key);
        $decrypted = $this->encrypter->decrypt($encrypted, $this->key);

        $this->assertEquals($originalValue, $decrypted);
    }

    #[Test]
    public function encrypt_decrypt_array() : void
    {
        $originalValue = ['key1' => 'value1', 'key2' => 'value2', 'nested' => ['a', 'b', 'c']];

        $encrypted = $this->encrypter->encrypt($originalValue, $this->key);
        $decrypted = $this->encrypter->decrypt($encrypted, $this->key);

        $this->assertEquals(serialize($originalValue), $decrypted);
    }

    #[Test]
    public function encrypt_produces_different_ciphertext_each_time() : void
    {
        $value = 'same value';

        $encrypted1 = $this->encrypter->encrypt($value, $this->key);
        $encrypted2 = $this->encrypter->encrypt($value, $this->key);

        // Due to random IV, ciphertext should be different
        $this->assertNotEquals($encrypted1->cipherText(), $encrypted2->cipherText());
        $this->assertNotEquals($encrypted1->iv(), $encrypted2->iv());
    }

    #[Test]
    public function encrypter_supports_aes_256_gcm() : void
    {
        $this->assertTrue($this->encrypter->supports('aes-256-gcm'));
        $this->assertFalse($this->encrypter->supports('aes-256-cbc'));
        $this->assertFalse($this->encrypter->supports('invalid-cipher'));
    }

    #[Test]
    public function tampered_cipher_text_throws_decryption_failed() : void
    {
        $originalValue = 'Secret data';
        $encrypted     = $this->encrypter->encrypt($originalValue, $this->key);

        // Tamper with the cipher text
        $tamperedCipherText = $encrypted->cipherText() ^ "\x01";

        $tamperedPayload = new EncryptedPayload(
            $tamperedCipherText,
            $encrypted->iv(),
            $encrypted->tag(),
            $encrypted->keyVersion()
        );

        $this->expectException(DecryptionFailed::class);
        $this->encrypter->decrypt($tamperedPayload, $this->key);
    }

    // ==========================================
    // Tamper Detection Tests
    // ==========================================

    #[Test]
    public function tampered_iv_throws_decryption_failed() : void
    {
        $originalValue = 'Secret data';
        $encrypted     = $this->encrypter->encrypt($originalValue, $this->key);

        // Tamper with the IV
        $tamperedIv = $encrypted->iv() ^ "\x01";

        $tamperedPayload = new EncryptedPayload(
            $encrypted->cipherText(),
            $tamperedIv,
            $encrypted->tag(),
            $encrypted->keyVersion()
        );

        $this->expectException(DecryptionFailed::class);
        $this->encrypter->decrypt($tamperedPayload, $this->key);
    }

    #[Test]
    public function tampered_tag_throws_decryption_failed() : void
    {
        $originalValue = 'Secret data';
        $encrypted     = $this->encrypter->encrypt($originalValue, $this->key);

        // Tamper with the authentication tag
        $tamperedTag = $encrypted->tag() ^ "\x01";

        $tamperedPayload = new EncryptedPayload(
            $encrypted->cipherText(),
            $encrypted->iv(),
            $tamperedTag,
            $encrypted->keyVersion()
        );

        $this->expectException(DecryptionFailed::class);
        $this->encrypter->decrypt($tamperedPayload, $this->key);
    }

    #[Test]
    public function tampered_serialized_payload_throws_decryption_failed() : void
    {
        $originalValue = 'Secret data';
        $encrypted     = $this->encrypter->encrypt($originalValue, $this->key);
        $serialized    = $encrypted->serialize();

        // Tamper with the serialized payload (modify cipher text portion)
        $parts              = explode(':', $serialized);
        $tamperedCipherText = base64_encode(base64_decode($parts[1]) ^ "\x01");
        $parts[1]           = $tamperedCipherText;
        $tamperedSerialized = implode(':', $parts);

        $this->expectException(DecryptionFailed::class);

        $payload = EncryptedPayload::deserialize($tamperedSerialized);
        $this->encrypter->decrypt($payload, $this->key);
    }

    #[Test]
    public function wrong_key_throws_decryption_failed() : void
    {
        $originalValue = 'Secret data';
        $encrypted     = $this->encrypter->encrypt($originalValue, $this->key);

        // Create a different key
        $wrongKey = EncryptionKey::generate('1');

        $this->expectException(DecryptionFailed::class);
        $this->encrypter->decrypt($encrypted, $wrongKey);
    }

    // ==========================================
    // Wrong Key Tests
    // ==========================================

    #[Test]
    public function key_resolver_returns_current_key() : void
    {
        $key1 = EncryptionKey::generate('1');
        $key2 = EncryptionKey::generate('2');

        $resolver = new KeyResolver(
            ['1' => $key1->toBase64(), '2' => $key2->toBase64()],
            '1'
        );

        $this->assertEquals($key1->toBase64(), $resolver->getCurrentKey()->toBase64());
        $this->assertEquals('1', $resolver->getCurrentVersion());
    }

    // ==========================================
    // KeyResolver Tests
    // ==========================================

    #[Test]
    public function key_resolver_returns_key_by_version() : void
    {
        $key1 = EncryptionKey::generate('1');
        $key2 = EncryptionKey::generate('2');

        $resolver = new KeyResolver(
            ['1' => $key1->toBase64(), '2' => $key2->toBase64()],
            '1'
        );

        $this->assertEquals($key1->toBase64(), $resolver->getKeyByVersion('1')->toBase64());
        $this->assertEquals($key2->toBase64(), $resolver->getKeyByVersion('2')->toBase64());
        $this->assertNull($resolver->getKeyByVersion('3'));
    }

    #[Test]
    public function key_resolver_throws_on_invalid_current_version() : void
    {
        $this->expectException(InvalidArgumentException::class);

        new KeyResolver(['1' => EncryptionKey::generate('1')->toBase64()], '99');
    }

    #[Test]
    public function key_resolver_add_key() : void
    {
        $key1     = EncryptionKey::generate('1');
        $resolver = new KeyResolver(['1' => $key1->toBase64()], '1');

        $key2 = EncryptionKey::generate('2');
        $resolver->addKey($key2);

        $this->assertEquals($key2->toBase64(), $resolver->getKeyByVersion('2')->toBase64());
    }

    #[Test]
    public function key_resolver_set_current_version() : void
    {
        $key1     = EncryptionKey::generate('1');
        $key2     = EncryptionKey::generate('2');
        $resolver = new KeyResolver(
            ['1' => $key1->toBase64(), '2' => $key2->toBase64()],
            '1'
        );

        $resolver->setCurrentVersion('2');

        $this->assertEquals('2', $resolver->getCurrentVersion());
        $this->assertEquals($key2->toBase64(), $resolver->getCurrentKey()->toBase64());
    }

    #[Test]
    public function key_resolver_set_invalid_version_throws() : void
    {
        $key1     = EncryptionKey::generate('1');
        $resolver = new KeyResolver(['1' => $key1->toBase64()], '1');

        $this->expectException(InvalidArgumentException::class);
        $resolver->setCurrentVersion('99');
    }

    #[Test]
    public function key_rotation_decrypts_old_data_with_old_key() : void
    {
        $key1 = EncryptionKey::generate('1');
        $key2 = EncryptionKey::generate('2');

        $resolver  = new KeyResolver(['1' => $key1->toBase64()], '1');
        $encrypter = new Encrypter();

        // Encrypt with key1
        $originalValue = 'Secret data before rotation';
        $encrypted     = $encrypter->encrypt($originalValue, $key1);

        // Add key2 and rotate
        $resolver->addKey($key2);
        $resolver->setCurrentVersion('2');

        // Can still decrypt old data with key1
        $decrypted = $encrypter->decrypt($encrypted, $key1);
        $this->assertEquals($originalValue, $decrypted);

        // New encryption uses key2
        $newValue     = 'Secret data after rotation';
        $newEncrypted = $encrypter->encrypt($newValue, $key2);
        $newDecrypted = $encrypter->decrypt($newEncrypted, $key2);
        $this->assertEquals($newValue, $newDecrypted);
    }

    // ==========================================
    // Key Rotation Tests
    // ==========================================

    #[Test]
    public function encrypt_value_flow() : void
    {
        $key       = EncryptionKey::generate('1');
        $resolver  = new KeyResolver(['1' => $key->toBase64()], '1');
        $encrypter = new Encrypter();

        $flow    = new EncryptValue($encrypter, $resolver);
        $payload = $flow->execute('Test value');

        $this->assertInstanceOf(EncryptedPayload::class, $payload);
        $this->assertEquals('1', $payload->keyVersion());
    }

    // ==========================================
    // Flow Tests
    // ==========================================

    #[Test]
    public function decrypt_value_flow() : void
    {
        $key       = EncryptionKey::generate('1');
        $resolver  = new KeyResolver(['1' => $key->toBase64()], '1');
        $encrypter = new Encrypter();

        $encryptFlow = new EncryptValue($encrypter, $resolver);
        $decryptFlow = new DecryptValue($encrypter, $resolver);

        $originalValue = 'Test value for flow';
        $encrypted     = $encryptFlow->execute($originalValue);

        $decrypted = $decryptFlow->execute($encrypted);

        // The encrypter returns the raw decrypted value; strings are not serialized
        $this->assertEquals($originalValue, $decrypted);
    }

    #[Test]
    public function decrypt_value_flow_with_serialized_payload() : void
    {
        $key       = EncryptionKey::generate('1');
        $resolver  = new KeyResolver(['1' => $key->toBase64()], '1');
        $encrypter = new Encrypter();

        $encryptFlow = new EncryptValue($encrypter, $resolver);
        $decryptFlow = new DecryptValue($encrypter, $resolver);

        $originalValue = 'Test value';
        $encrypted     = $encryptFlow->execute($originalValue);
        $serialized    = $encrypted->serialize();

        $decrypted = $decryptFlow->execute($serialized);

        // The encrypter returns the raw decrypted value; strings are not serialized
        $this->assertEquals($originalValue, $decrypted);
    }

    #[Test]
    public function decrypt_value_flow_with_missing_key_version() : void
    {
        $key1      = EncryptionKey::generate('1');
        $key2      = EncryptionKey::generate('2');
        $resolver  = new KeyResolver(['1' => $key1->toBase64()], '1');
        $encrypter = new Encrypter();

        // Encrypt with key2 (not in resolver)
        $encrypted = $encrypter->encrypt('Secret', $key2);

        $decryptFlow = new DecryptValue($encrypter, $resolver);

        $this->expectException(DecryptionFailed::class);
        $decryptFlow->execute($encrypted);
    }

    #[Test]
    public function encryption_public_api_encrypt_and_decrypt() : void
    {
        $key       = EncryptionKey::generate('1');
        $resolver  = new KeyResolver(['1' => $key->toBase64()], '1');
        $encrypter = new Encrypter();

        $encryptFlow = new EncryptValue($encrypter, $resolver);
        $decryptFlow = new DecryptValue($encrypter, $resolver);

        $encryption = new Encryption($encryptFlow, $decryptFlow, $resolver);

        $originalValue = ['user' => 'john', 'email' => 'john@example.com'];
        $encrypted     = $encryption->encrypt($originalValue);

        $this->assertIsString($encrypted);
        $this->assertStringStartsWith('v1:', $encrypted);

        $decrypted = $encryption->decrypt($encrypted);
        $this->assertEquals($originalValue, $decrypted);
    }

    // ==========================================
    // Public API (Encryption) Tests
    // ==========================================

    #[Test]
    public function encryption_public_api_rotate_keys() : void
    {
        $key1      = EncryptionKey::generate('1');
        $resolver  = new KeyResolver(['1' => $key1->toBase64()], '1');
        $encrypter = new Encrypter();

        $encryptFlow = new EncryptValue($encrypter, $resolver);
        $decryptFlow = new DecryptValue($encrypter, $resolver);

        $encryption = new Encryption($encryptFlow, $decryptFlow, $resolver);

        // Encrypt with key1
        $oldValue     = 'Old data';
        $oldEncrypted = $encryption->encrypt($oldValue);

        // Rotate keys
        $key2 = EncryptionKey::generate('2');
        $encryption->rotateKeys($key2);

        $this->assertEquals('2', $encryption->getCurrentKeyVersion());

        // Can still decrypt old data
        $this->assertEquals($oldValue, $encryption->decrypt($oldEncrypted));

        // New encryption uses key2
        $newValue     = 'New data';
        $newEncrypted = $encryption->encrypt($newValue);
        $this->assertEquals($newValue, $encryption->decrypt($newEncrypted));
    }

    #[Test]
    public function encryption_public_api_tamper_detection() : void
    {
        $key       = EncryptionKey::generate('1');
        $resolver  = new KeyResolver(['1' => $key->toBase64()], '1');
        $encrypter = new Encrypter();

        $encryptFlow = new EncryptValue($encrypter, $resolver);
        $decryptFlow = new DecryptValue($encrypter, $resolver);

        $encryption = new Encryption($encryptFlow, $decryptFlow, $resolver);

        $originalValue = 'Secret data';
        $encrypted     = $encryption->encrypt($originalValue);

        // Tamper with the serialized payload
        $parts              = explode(':', $encrypted);
        $tamperedCipherText = base64_encode(base64_decode($parts[1]) ^ "\x01");
        $parts[1]           = $tamperedCipherText;
        $tamperedEncrypted  = implode(':', $parts);

        $this->expectException(DecryptionFailed::class);
        $encryption->decrypt($tamperedEncrypted);
    }

    #[Test]
    public function encryption_configuration_from_array() : void
    {
        $key = EncryptionKey::generate('1');

        $config = EncryptionConfiguration::fromArray([
                                                         'cipher'          => 'aes-256-gcm',
                                                         'key_versions'    => ['1' => $key->toBase64()],
                                                         'current_version' => '1',
                                                         'salt'            => 'my-salt',
                                                     ]);

        $this->assertEquals('aes-256-gcm', $config->cipher());
        $this->assertEquals(['1' => $key->toBase64()], $config->keyVersions());
        $this->assertEquals('1', $config->currentVersion());
        $this->assertEquals('my-salt', $config->salt());
    }

    // ==========================================
    // Configuration Tests
    // ==========================================

    #[Test]
    public function encryption_configuration_to_array() : void
    {
        $key = EncryptionKey::generate('1');

        $config = new EncryptionConfiguration(
            cipher        : 'aes-256-gcm',
            keyVersions   : ['1' => $key->toBase64()],
            currentVersion: '1',
            salt          : 'my-salt'
        );

        $array = $config->toArray();

        $this->assertEquals('aes-256-gcm', $array['cipher']);
        $this->assertEquals(['1' => $key->toBase64()], $array['key_versions']);
        $this->assertEquals('1', $array['current_version']);
        $this->assertEquals('my-salt', $array['salt']);
    }

    #[Test]
    public function encryption_configuration_requires_key_versions() : void
    {
        $this->expectException(InvalidArgumentException::class);

        new EncryptionConfiguration(keyVersions: []);
    }

    #[Test]
    public function encryption_configuration_requires_valid_current_version() : void
    {
        $key = EncryptionKey::generate('1');

        $this->expectException(InvalidArgumentException::class);

        new EncryptionConfiguration(
            keyVersions   : ['1' => $key->toBase64()],
            currentVersion: '99'
        );
    }

    #[Test]
    public function encryption_configuration_default_cipher() : void
    {
        $key = EncryptionKey::generate('1');

        $config = EncryptionConfiguration::fromArray([
                                                         'key_versions' => ['1' => $key->toBase64()],
                                                     ]);

        $this->assertEquals('aes-256-gcm', $config->cipher());
    }

    protected function setUp() : void
    {
        $this->key       = EncryptionKey::generate('1');
        $this->encrypter = new Encrypter();
    }
}
