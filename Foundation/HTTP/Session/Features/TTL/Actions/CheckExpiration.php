<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\TTL\Actions;

use Avax\HTTP\Session\Storage\SessionStore;

/**
 * CheckExpiration Action
 *
 * Single Responsibility: Check if a session value has expired.
 *
 * This action validates TTL metadata and determines if a value should
 * be considered expired based on its expiration timestamp.
 *
 * Enterprise Rules:
 * - Automatic cleanup: Expired values are removed.
 * - Precision: Uses Unix timestamp comparison.
 * - Fail-safe: Missing metadata means no expiration.
 *
 * Usage:
 *   $action = new CheckExpiration($store);
 *   $isExpired = $action->execute('user_token');
 *
 * @package Avax\HTTP\Session\Features\TTL\Actions
 */
final readonly class CheckExpiration
{
    /**
     * CheckExpiration Constructor.
     *
     * @param SessionStore $store The session storage backend.
     */
    public function __construct(
        private SessionStore $store
    ) {}

    /**
     * Execute the action: Check if value has expired.
     *
     * This method:
     * 1. Retrieves TTL metadata
     * 2. Compares expiration timestamp with current time
     * 3. Removes expired values automatically
     *
     * @param string $key The session key to check.
     *
     * @return bool True if expired, false otherwise.
     */
    public function execute(string $key): bool
    {
        // Construct metadata key.
        $metaKey = "{$key}::__meta";

        // Retrieve metadata.
        $meta = $this->store->get(key: $metaKey, default: []);

        // If no expiration metadata, value never expires.
        if (!isset($meta['expires_at'])) {
            return false;
        }

        // Get current timestamp.
        $now = time();

        // Check if value has expired.
        $isExpired = $now >= $meta['expires_at'];

        // If expired, perform automatic cleanup.
        if ($isExpired) {
            // Remove the expired value.
            $this->store->delete(key: $key);

            // Remove the metadata.
            $this->store->delete(key: $metaKey);

            // Log expiration event.
            logger()?->debug(
                message: 'Session value expired and removed',
                context: [
                    'key' => $key,
                    'expired_at' => $meta['expires_at'],
                    'current_time' => $now,
                    'action' => 'CheckExpiration',
                ]
            );
        }

        return $isExpired;
    }
}
