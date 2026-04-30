<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Capabilities\Encryption;

use InvalidArgumentException;

/**
 * Value object representing an encryption key for AES-256.
 */
final readonly class EncryptionKey
{
    private const KEY_LENGTH = 32; // 256 bits for AES-256

    public function __construct(
        private string $keyMaterial,
        private string $version = '1',
    )
    {
        if (strlen($this->keyMaterial) !== self::KEY_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Encryption key must be exactly %d bytes, got %d', self::KEY_LENGTH, strlen($this->keyMaterial))
            );
        }
    }

    /**
     * Generate a new random encryption key.
     */
    public static function generate(string $version = '1') : self
    {
        return new self(random_bytes(self::KEY_LENGTH), $version);
    }

    /**
     * Create an encryption key from a base64-encoded string.
     */
    public static function fromBase64(string $base64, string $version = '1') : self
    {
        $keyMaterial = base64_decode($base64, true);

        if ($keyMaterial === false) {
            throw new InvalidArgumentException('Invalid base64-encoded encryption key');
        }

        return new self($keyMaterial, $version);
    }

    /**
     * Convert the encryption key to a base64-encoded string.
     */
    public function toBase64() : string
    {
        return base64_encode($this->keyMaterial);
    }

    /**
     * Check if the encryption key is valid (correct length).
     */
    public function isValid() : bool
    {
        return strlen($this->keyMaterial) === self::KEY_LENGTH;
    }

    /**
     * Get the raw key material.
     */
    public function raw() : string
    {
        return $this->keyMaterial;
    }

    /**
     * Get the key version identifier.
     */
    public function version() : string
    {
        return $this->version;
    }
}
