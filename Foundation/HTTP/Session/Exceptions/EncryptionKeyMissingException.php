<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Exceptions;

/**
 * EncryptionKeyMissingException
 *
 * Thrown when encryption is required but no key is configured.
 *
 * Use Case:
 * - Attempting to store _secure suffixed keys without encryption key
 * - Using secure() scope without encryption key
 *
 * @example
 *   try {
 *       $session->put('token_secure', 'value');
 *   } catch (EncryptionKeyMissingException $e) {
 *       // Handle missing key
 *   }
 *
 * @package Avax\HTTP\Session\Exceptions
 */
final class EncryptionKeyMissingException extends SessionException
{
    /**
     * Create exception for missing encryption key.
     *
     * @return self
     */
    public static function create() : self
    {
        return new self(
            'Encryption key is required for secure session values. ' .
            'Set encryption key in SessionConfig or SessionManager constructor.'
        );
    }
}
