<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Crypto\Actions;

use Avax\Security\Encryption\Contracts\EncrypterInterface;

/**
 * EncryptValue Action
 *
 * Single Responsibility: Encrypt a session value for secure storage.
 *
 * This action delegates to the encryption service to protect sensitive
 * session data from unauthorized access.
 *
 * Enterprise Rules:
 * - Security: Uses AES-256-GCM encryption.
 * - Validation: Ensures value is serializable.
 * - Error handling: Throws on encryption failure.
 *
 * Usage:
 *   $action = new EncryptValue($encrypter);
 *   $encrypted = $action->execute($sensitiveData);
 *
 * @package Avax\HTTP\Session\Features\Crypto\Actions
 */
final readonly class EncryptValue
{
    /**
     * EncryptValue Constructor.
     *
     * @param EncrypterInterface $encrypter The encryption service.
     */
    public function __construct(
        private EncrypterInterface $encrypter
    ) {}

    /**
     * Execute the action: Encrypt value.
     *
     * This method:
     * 1. Serializes the value if needed
     * 2. Encrypts using the encryption service
     * 3. Returns encrypted string
     *
     * @param mixed $value The value to encrypt.
     *
     * @return string The encrypted value.
     *
     * @throws \RuntimeException If encryption fails.
     */
    public function execute(mixed $value): string
    {
        try {
            // Delegate encryption to the encryption service.
            // The encrypter handles serialization internally.
            $encrypted = $this->encrypter->encrypt($value);

            // Log encryption event (without sensitive data).
            logger()?->debug(
                message: 'Session value encrypted',
                context: [
                    'value_type' => get_debug_type($value),
                    'action' => 'EncryptValue',
                    'security_event' => true,
                ]
            );

            return $encrypted;
        } catch (\Throwable $e) {
            // Log encryption failure.
            logger()?->error(
                message: 'Session value encryption failed',
                context: [
                    'error' => $e->getMessage(),
                    'action' => 'EncryptValue',
                    'security_event' => true,
                ]
            );

            // Re-throw as RuntimeException.
            throw new \RuntimeException(
                message: 'Failed to encrypt session value: ' . $e->getMessage(),
                previous: $e
            );
        }
    }
}
