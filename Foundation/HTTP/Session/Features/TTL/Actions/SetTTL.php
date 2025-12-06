<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\TTL\Actions;

use Avax\HTTP\Session\Storage\SessionStore;

/**
 * SetTTL Action
 *
 * Single Responsibility: Set Time-To-Live metadata for a session value.
 *
 * This action manages expiration metadata, allowing session values to
 * automatically expire after a specified duration.
 *
 * Enterprise Rules:
 * - Validation: TTL must be positive.
 * - Atomicity: Metadata storage is atomic.
 * - Precision: Uses Unix timestamp for exact expiration.
 *
 * Usage:
 *   $action = new SetTTL($store);
 *   $action->execute('user_token', 3600); // Expires in 1 hour
 *
 * @package Avax\HTTP\Session\Features\TTL\Actions
 */
final readonly class SetTTL
{
    /**
     * SetTTL Constructor.
     *
     * @param SessionStore $store The session storage backend.
     */
    public function __construct(
        private SessionStore $store
    ) {}

    /**
     * Execute the action: Set TTL metadata.
     *
     * This method:
     * 1. Validates TTL value
     * 2. Calculates expiration timestamp
     * 3. Stores metadata
     *
     * @param string $key The session key.
     * @param int    $ttl Time-to-live in seconds.
     *
     * @return void
     */
    public function execute(string $key, int $ttl): void
    {
        // Guard: Validate TTL is positive.
        if ($ttl <= 0) {
            throw new \InvalidArgumentException(
                message: "TTL must be positive, got: {$ttl}"
            );
        }

        // Calculate expiration timestamp.
        $expiresAt = time() + $ttl;

        // Construct metadata key.
        $metaKey = "{$key}::__meta";

        // Get existing metadata or create new.
        $meta = $this->store->get(key: $metaKey, default: []);

        // Set expiration timestamp.
        $meta['expires_at'] = $expiresAt;

        // Store updated metadata.
        $this->store->put(key: $metaKey, value: $meta);

        // Log TTL setting.
        logger()?->debug(
            message: 'TTL metadata set',
            context: [
                'key' => $key,
                'ttl_seconds' => $ttl,
                'expires_at' => $expiresAt,
                'action' => 'SetTTL',
            ]
        );
    }
}
