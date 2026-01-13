<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing\Exceptions;

/**
 * Base interface for all router-related exceptions.
 *
 * Ensures consistent exception taxonomy across the entire router component.
 * All router exceptions must implement this interface for proper error handling
 * and debugging consistency.
 */
interface RouterExceptionInterface extends \Throwable
{
    /**
     * Get the HTTP status code associated with this exception.
     *
     * Provides standardized HTTP status codes for router exceptions
     * to enable proper HTTP response generation.
     */
    public function getHttpStatusCode() : int;

    /**
     * Get additional context information for debugging.
     *
     * Returns structured context data that can be used for logging,
     * monitoring, and error reporting.
     *
     * @return array<string, mixed>
     */
    public function getContext() : array;

    /**
     * Check if this exception is retryable.
     *
     * Indicates whether the operation that caused this exception
     * can be safely retried.
     */
    public function isRetryable() : bool;
}