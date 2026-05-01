<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Configuration;

use InvalidArgumentException;

/**
 * Configuration for the encryption system.
 *
 * @property-read string                $cipher         The encryption cipher algorithm (e.g., 'aes-256-gcm')
 * @property-read array<string, string> $keyVersions    Map of version => base64-encoded key
 * @property-read string                $currentVersion The current active key version
 * @property-read string                $salt           Optional salt for key derivation
 */
final readonly class EncryptionConfiguration
{
    private const DEFAULT_CIPHER = 'aes-256-gcm';

    public function __construct(
        private string $cipher = self::DEFAULT_CIPHER,
        private array $keyVersions = [],
        private string $currentVersion = '1',
        private string $salt = '',
    ) {
        if (empty($this->keyVersions)) {
            throw new InvalidArgumentException('Encryption configuration requires at least one key version');
        }

        if (! isset($this->keyVersions[$this->currentVersion])) {
            throw new InvalidArgumentException(
                sprintf('Current key version "%s" not found in key versions', $this->currentVersion),
            );
        }
    }

    /**
     * Create configuration from an array.
     *
     * @param array{
     *     cipher?: string,
     *     key_versions: array<string, string>,
     *     current_version?: string,
     *     salt?: string
     * } $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            cipher        : $config['cipher'] ?? self::DEFAULT_CIPHER,
            keyVersions   : $config['key_versions'],
            currentVersion: $config['current_version'] ?? '1',
            salt          : $config['salt'] ?? '',
        );
    }

    /**
     * Get the encryption cipher algorithm.
     */
    public function cipher(): string
    {
        return $this->cipher;
    }

    /**
     * Get all key versions.
     *
     * @return array<string, string>
     */
    public function keyVersions(): array
    {
        return $this->keyVersions;
    }

    /**
     * Get the current active key version identifier.
     */
    public function currentVersion(): string
    {
        return $this->currentVersion;
    }

    /**
     * Get the salt for key derivation.
     */
    public function salt(): string
    {
        return $this->salt;
    }

    /**
     * Convert configuration to array.
     *
     * @return array{
     *     cipher: string,
     *     key_versions: array<string, string>,
     *     current_version: string,
     *     salt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'cipher'       => $this->cipher,
            'key_versions' => $this->keyVersions,
            'current_version' => $this->currentVersion,
            'salt'         => $this->salt,
        ];
    }
}
