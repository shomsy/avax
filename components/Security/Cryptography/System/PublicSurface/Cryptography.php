<?php

declare(strict_types=1);

namespace Avax\Components\Security\Cryptography\System\PublicSurface;

use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\EncryptionKey;
use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\KeyResolver;
use Avax\Components\Security\Cryptography\System\Flows\DecryptValue\DecryptValue;
use Avax\Components\Security\Cryptography\System\Flows\EncryptValue\EncryptValue;
use Avax\Components\Security\Cryptography\System\Foundation\Failure\DecryptionFailed;

/**
 * Cryptography - Public API for encryption operations.
 *
 * Provides high-level encrypt/decrypt methods that handle
 * serialization, key resolution, and key rotation.
 */
final readonly class Cryptography
{
    public function __construct(
        private EncryptValue $encryptValue,
        private DecryptValue $decryptValue,
        private KeyResolver  $keyResolver,
    ) {}

    /**
     * Encrypt a value and return a serialized encrypted payload string.
     *
     * @param mixed $value The value to encrypt (string, array, object, etc.)
     *
     * @return string Serialized encrypted payload
     */
    public function encrypt(mixed $value) : string
    {
        $encryptedPayload = $this->encryptValue->execute($value);

        return $encryptedPayload->serialize();
    }

    /**
     * Decrypt a serialized encrypted payload string back to the original value.
     *
     * @param string $serialized The serialized encrypted payload
     *
     * @return mixed The decrypted value
     *
     * @throws DecryptionFailed if decryption fails or payload has been tampered with
     */
    public function decrypt(string $serialized) : mixed
    {
        return $this->decryptValue->execute($serialized);
    }

    /**
     * Rotate encryption keys by adding a new key and setting it as current.
     *
     * @param EncryptionKey $encryptionKey The new encryption key to use for future encryptions
     */
    public function rotateKeys(EncryptionKey $encryptionKey) : void
    {
        $this->keyResolver->addKey($encryptionKey);
        $this->keyResolver->setCurrentVersion($encryptionKey->version());
    }

    /**
     * Get the current key version being used for encryption.
     */
    public function getCurrentKeyVersion() : string
    {
        return $this->keyResolver->getCurrentVersion();
    }
}
