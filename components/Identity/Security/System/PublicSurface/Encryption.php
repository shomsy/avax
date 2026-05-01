<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\PublicSurface;

use Avax\Components\Identity\Security\System\Capabilities\Encryption\EncryptionKey;
use Avax\Components\Identity\Security\System\Capabilities\Encryption\KeyResolver;
use Avax\Components\Identity\Security\System\Flows\DecryptValue\DecryptValue;
use Avax\Components\Identity\Security\System\Flows\EncryptValue\EncryptValue;
use Avax\Components\Identity\Security\System\Foundation\Failure\DecryptionFailed;
use Throwable;

/**
 * Encryption - Public API for encryption operations.
 *
 * Provides high-level encrypt/decrypt methods that handle
 * serialization, key resolution, and key rotation.
 */
final readonly class Encryption
{
    public function __construct(
        private EncryptValue $encryptFlow,
        private DecryptValue $decryptFlow,
        private KeyResolver $keyResolver,
    ) {}

    /**
     * Encrypt a value and return a serialized encrypted payload string.
     *
     * @param  mixed  $value  The value to encrypt (string, array, object, etc.)
     * @return string Serialized encrypted payload
     */
    public function encrypt(mixed $value): string
    {
        $payload = $this->encryptFlow->execute($value);

        return $payload->serialize();
    }

    /**
     * Decrypt a serialized encrypted payload string back to the original value.
     *
     * @param  string  $serialized  The serialized encrypted payload
     * @return mixed The decrypted value
     *
     * @throws DecryptionFailed if decryption fails or payload has been tampered with
     */
    public function decrypt(string $serialized): mixed
    {
        $plaintext = $this->decryptFlow->execute($serialized);

        // Try JSON decode first (primary format)
        try {
            $decoded = json_decode($plaintext, true, 512, JSON_THROW_ON_ERROR);

            return $decoded;
        } catch (Throwable $e) {
            // If JSON fails, try unserialize for backward compatibility
            try {
                $unserialized = unserialize($plaintext, ['allowed_classes' => false]);
                if ($unserialized !== false) {
                    return $unserialized;
                }
            } catch (Throwable) {
                // Ignore unserialize errors, fall through to last resort
            }

            // Last resort: return as plain string
            return $plaintext;
        }
    }

    /**
     * Rotate encryption keys by adding a new key and setting it as current.
     *
     * @param  EncryptionKey  $newKey  The new encryption key to use for future encryptions
     */
    public function rotateKeys(EncryptionKey $newKey): void
    {
        $this->keyResolver->addKey($newKey);
        $this->keyResolver->setCurrentVersion($newKey->version());
    }

    /**
     * Get the current key version being used for encryption.
     */
    public function getCurrentKeyVersion(): string
    {
        return $this->keyResolver->getCurrentVersion();
    }
}
