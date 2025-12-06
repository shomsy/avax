<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\TTL;

/**
 * TTLManagerInterface
 *
 * Contract for TTL management.
 *
 * @package Avax\HTTP\Session\Features\TTL
 */
interface TTLManagerInterface
{
    /**
     * Touch a key with TTL.
     *
     * @param string $key     The key.
     * @param int    $seconds TTL in seconds.
     *
     * @return void
     */
    public function touch(string $key, int $seconds): void;

    /**
     * Check if key has expired.
     *
     * @param string $key The key.
     *
     * @return bool
     */
    public function hasExpired(string $key): bool;

    /**
     * Get expiration timestamp.
     *
     * @param string $key The key.
     *
     * @return int|null
     */
    public function getExpiration(string $key): int|null;

    /**
     * Cleanup expired keys.
     *
     * @return int Number of keys cleaned up.
     */
    public function cleanup(): int;
}
