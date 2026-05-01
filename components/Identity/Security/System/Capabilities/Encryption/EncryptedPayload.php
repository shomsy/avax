<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Capabilities\Encryption;

use Avax\Components\Identity\Security\System\Foundation\Failure\DecryptionFailed;

/**
 * Immutable encrypted payload containing cipher text, IV, auth tag, and key version.
 *
 * Serialized format: VERSION:cipherTextBase64:ivBase64:tagBase64
 * This format is tamper-evident - any modification will cause decryption to fail.
 */
final readonly class EncryptedPayload
{
    private const SEPARATOR = ':';

    private const VERSION_PREFIX = 'v1';

    public function __construct(
        private string $cipherText,
        private string $iv,
        private string $tag,
        private string $keyVersion = '1',
    ) {}

    /**
     * Deserialize a string representation into an EncryptedPayload.
     *
     * @throws DecryptionFailed if the payload format is invalid or tampered
     */
    public static function deserialize(string $serialized): self
    {
        $parts = explode(self::SEPARATOR, $serialized);

        if (count($parts) !== 5) {
            throw new DecryptionFailed('Invalid encrypted payload format: expected 5 parts, got '.count($parts));
        }

        [$version, $cipherTextB64, $ivB64, $tagB64, $keyVersion] = $parts;

        if ($version !== self::VERSION_PREFIX) {
            throw new DecryptionFailed('Unknown payload version: '.$version);
        }

        $cipherText = base64_decode($cipherTextB64, true);
        $iv = base64_decode($ivB64, true);
        $tag = base64_decode($tagB64, true);

        if ($cipherText === false || $iv === false || $tag === false) {
            throw new DecryptionFailed('Invalid encrypted payload: contains malformed base64 data');
        }

        return new self($cipherText, $iv, $tag, $keyVersion);
    }

    /**
     * Get the encrypted cipher text.
     */
    public function cipherText(): string
    {
        return $this->cipherText;
    }

    /**
     * Get the initialization vector.
     */
    public function iv(): string
    {
        return $this->iv;
    }

    /**
     * Get the authentication tag (GCM).
     */
    public function tag(): string
    {
        return $this->tag;
    }

    /**
     * Get the key version used for encryption.
     */
    public function keyVersion(): string
    {
        return $this->keyVersion;
    }

    /**
     * Serialize the payload to a string representation.
     *
     * Format: v1:<cipherTextBase64>:<ivBase64>:<tagBase64>:<keyVersion>
     */
    public function serialize(): string
    {
        return implode(self::SEPARATOR, [
            self::VERSION_PREFIX,
            base64_encode($this->cipherText),
            base64_encode($this->iv),
            base64_encode($this->tag),
            $this->keyVersion,
        ]);
    }
}
