<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Crypto\Actions;

use Avax\Security\Encryption\Contracts\EncrypterInterface;

/**
 * DecryptValue Action
 *
 * Single Responsibility: Decrypt an encrypted session value.
 *
 * This action delegates to the encryption service to decrypt and restore
 * session data that was previously encrypted.
 *
 * Enterprise Rules:
 * - Security: Validates encrypted data integrity.
 * - Error handling: Throws on decryption failure (tampering detection).
 * - Logging: Records decryption failures for security monitoring.
 *
 * Usage:
 *   $action = new DecryptValue($encrypter);
 *   $decrypted = $action->execute($encryptedString);
 *
 * @package Avax\HTTP\Session\Features\Crypto\Actions
 */
final readonly class DecryptValue
{
    /**
     * DecryptValue Constructor.
     *
     * @param EncrypterInterface $encrypter The encryption service.
     */
    public function __construct(
        private EncrypterInterface $encrypter
    ) {}

    /**
     * Execute the action: Decrypt value.
     *
     * This method:
     * 1. Validates encrypted string format
     * 2. Decrypts using the encryption service
     * 3. Returns original value
     *
     * @param string $encryptedValue The encrypted value.
     *
     * @return mixed The decrypted original value.
     *
     * @throws \RuntimeException If decryption fails (tampering or corruption).
     */
    public function execute(string $encryptedValue): mixed
    {
        try {
            // Delegate decryption to the encryption service.
            // The encrypter handles deserialization internally.
            $decrypted = $this->encrypter->decrypt($encryptedValue);

            // Log decryption event (without sensitive data).
            logger()?->debug(
                message: 'Session value decrypted',
                context: [
                    'result_type' => get_debug_type($decrypted),
                    'action' => 'DecryptValue',
                    'security_event' => true,
                ]
            );

            return $decrypted;
        } catch (\Throwable $e) {
            // Log decryption failure as potential security event.
            // This could indicate tampering or data corruption.
            logger()?->warning(
                message: 'Session value decryption failed - possible tampering',
                context: [
                    'error' => $e->getMessage(),
                    'action' => 'DecryptValue',
                    'security_event' => true,
                    'severity' => 'high',
                ]
            );

            // Re-throw as RuntimeException.
            throw new \RuntimeException(
                message: 'Failed to decrypt session value: ' . $e->getMessage(),
                previous: $e
            );
        }
    }
}
