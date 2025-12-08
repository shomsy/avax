<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Contracts\Security;

/**
 * ServerContext - Server Environment Context
 *
 * Abstraction for accessing server variables.
 * Enables testability by decoupling from $_SERVER superglobal.
 * 
 * @package Avax\HTTP\Session\Contracts\Security
 */
interface ServerContext
{
    /**
     * Get User-Agent string.
     *
     * @return string User agent.
     */
    public function getUserAgent(): string;

    /**
     * Check if connection is secure (HTTPS).
     *
     * @return bool True if HTTPS.
     */
    public function isSecure(): bool;

    /**
     * Get client IP address.
     *
     * @return string IP address.
     */
    public function getClientIp(): string;
}
