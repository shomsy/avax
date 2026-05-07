<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Capabilities\Encryption;

use InvalidArgumentException;

/**
 * Resolves encryption keys from configuration, supporting multiple key versions for rotation.
 */
final class KeyResolver
{
    /**
     * @var array<string, EncryptionKey> Map of key version => EncryptionKey
     */
    private array $keys = [];

    /**
     * @var string The version of the current/active key for encryption
     */
    private string $currentVersion;

    /**
     * @param array<string, string> $keyVersions    Map of version => base64-encoded key
     * @param string                $currentVersion The version to use for new encryptions
     */
    public function __construct(
        array  $keyVersions,
        string $currentVersion,
    )
    {
        foreach ($keyVersions as $version => $base64Key) {
            $versionString              = (string) $version;
            $this->keys[$versionString] = EncryptionKey::fromBase64($base64Key, $versionString);
        }

        $currentVersionString = $currentVersion;
        if (! isset($this->keys[$currentVersionString])) {
            throw new InvalidArgumentException(
                sprintf('Current key version "%s" not found in available versions', $currentVersionString),
            );
        }

        $this->currentVersion = $currentVersionString;
    }

    /**
     * Get the current active encryption key (for new encryptions).
     */
    public function getCurrentKey() : EncryptionKey
    {
        return $this->keys[$this->currentVersion];
    }

    /**
     * Get a specific key by version (for decrypting older payloads).
     *
     * @param string $version The key version to retrieve
     *
     * @return EncryptionKey|null The encryption key or null if not found
     */
    public function getKeyByVersion(string $version) : ?EncryptionKey
    {
        return $this->keys[$version] ?? null;
    }

    /**
     * Get all available key versions.
     *
     * @return array<string, EncryptionKey>
     */
    public function getAllKeys() : array
    {
        return $this->keys;
    }

    /**
     * Get the current key version identifier.
     */
    public function getCurrentVersion() : string
    {
        return $this->currentVersion;
    }

    /**
     * Set the current active key version.
     */
    public function setCurrentVersion(string $version) : void
    {
        if (! isset($this->keys[$version])) {
            throw new InvalidArgumentException(
                sprintf('Cannot set current version to "%s": key not found', $version),
            );
        }

        $this->currentVersion = $version;
    }

    /**
     * Add a new key version (for key rotation).
     */
    public function addKey(EncryptionKey $encryptionKey) : void
    {
        $this->keys[$encryptionKey->version()] = $encryptionKey;
    }
}
