<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Exceptions;

/**
 * ExpiredSessionException
 *
 * Thrown when attempting to access an expired session value.
 * 
 * Use Case:
 * - TTL-based expiration
 * - Policy-based expiration
 * - Manual expiration checks
 * 
 * @example
 *   try {
 *       $value = $session->get('key');
 *   } catch (ExpiredSessionException $e) {
 *       // Handle expiration
 *   }
 *
 * @package Avax\HTTP\Session\Exceptions
 */
final class ExpiredSessionException extends SessionException
{
    /**
     * Create exception for expired key.
     *
     * @param string $key The expired key.
     *
     * @return self
     */
    public static function forKey(string $key): self
    {
        return new self("Session key '{$key}' has expired");
    }
}
