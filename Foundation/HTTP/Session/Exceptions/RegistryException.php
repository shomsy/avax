<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Exceptions;

use RuntimeException;

/**
 * RegistryException - Session Registry Errors
 *
 * Thrown when session registry operations fail.
 * Provides fine-grained error handling for multi-device session management.
 *
 * Error Types:
 * - Session not found
 * - Session already registered
 * - Concurrent session limit exceeded
 * - Revocation errors
 * - Device management errors
 *
 * @package Avax\HTTP\Session\Exceptions
 */
class RegistryException extends RuntimeException
{
    /**
     * Create exception for session not found.
     *
     * @param string $sessionId Session ID.
     *
     * @return self
     */
    public static function sessionNotFound(string $sessionId) : self
    {
        return new self("Session '{$sessionId}' not found in registry.");
    }

    /**
     * Create exception for session already registered.
     *
     * @param string $sessionId Session ID.
     *
     * @return self
     */
    public static function sessionAlreadyRegistered(string $sessionId) : self
    {
        return new self("Session '{$sessionId}' is already registered.");
    }

    /**
     * Create exception for concurrent session limit exceeded.
     *
     * @param string $userId  User identifier.
     * @param int    $limit   Maximum allowed sessions.
     * @param int    $current Current session count.
     *
     * @return self
     */
    public static function concurrentLimitExceeded(string $userId, int $limit, int $current) : self
    {
        return new self(
            "User '{$userId}' has exceeded concurrent session limit. " .
            "Limit: {$limit}, Current: {$current}"
        );
    }

    /**
     * Create exception for revoked session access attempt.
     *
     * @param string $sessionId Session ID.
     * @param string $reason    Revocation reason.
     *
     * @return self
     */
    public static function sessionRevoked(string $sessionId, string $reason) : self
    {
        return new self(
            "Session '{$sessionId}' has been revoked. Reason: {$reason}"
        );
    }

    /**
     * Create exception for revocation failure.
     *
     * @param string $sessionId Session ID.
     * @param string $reason    Failure reason.
     *
     * @return self
     */
    public static function revocationFailed(string $sessionId, string $reason) : self
    {
        return new self("Failed to revoke session '{$sessionId}': {$reason}");
    }

    /**
     * Create exception for device not found.
     *
     * @param string $userId    User identifier.
     * @param string $userAgent User agent string.
     *
     * @return self
     */
    public static function deviceNotFound(string $userId, string $userAgent) : self
    {
        return new self(
            "No sessions found for user '{$userId}' with device '{$userAgent}'."
        );
    }

    /**
     * Create exception for invalid session metadata.
     *
     * @param string $sessionId Session ID.
     * @param string $reason    Reason why metadata is invalid.
     *
     * @return self
     */
    public static function invalidMetadata(string $sessionId, string $reason) : self
    {
        return new self("Invalid metadata for session '{$sessionId}': {$reason}");
    }

    /**
     * Create exception for registry storage failure.
     *
     * @param string $operation Operation that failed.
     * @param string $reason    Failure reason.
     *
     * @return self
     */
    public static function storageFailed(string $operation, string $reason) : self
    {
        return new self("Registry storage operation '{$operation}' failed: {$reason}");
    }
}
