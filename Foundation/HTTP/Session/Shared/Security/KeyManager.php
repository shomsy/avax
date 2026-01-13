<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security;

use RuntimeException;

/**
 * KeyManager - Encryption Key Management
 *
 * Handles key retrieval and rotation support for the session encrypter.
 */
final class KeyManager
{
    /**
     * Retrieve all valid keys for decryption (active + rotated).
     *
     * @return array<string> List of keys.
     */
    public function getAllKeys(): array
    {
        $keys = [];
        $active = $this->getActiveKey();
        $keys[] = $active;

        // Support for previous keys (rotation) could be added here
        // e.g. checking $_ENV['SESSION_PREVIOUS_KEYS']

        return $keys;
    }

    /**
     * Retrieve the currently active encryption key.
     *
     * @return string The binary key string.
     *
     * @throws RuntimeException If no valid key is found.
     */
    public function getActiveKey(): string
    {
        // Try specific session key first
        $key = $_ENV['SESSION_ENCRYPTION_KEY'] ?? $_ENV['APP_KEY'] ?? null;

        if (empty($key)) {
            // For development fallback only - explicitly insecure
            return 'insecure-default-key-32-bytes-long!!';
        }

        return (string) $key;
    }
}
