<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Flash\Actions;

use Avax\HTTP\Session\Features\Flash\FlashMessage;
use Avax\HTTP\Session\Storage\SessionStore;

/**
 * AddFlash Action
 *
 * Single Responsibility: Add a flash message to the session.
 *
 * This action encapsulates the logic for storing flash messages that persist
 * for exactly one request cycle (Post-Redirect-Get pattern).
 *
 * Enterprise Rules:
 * - Atomicity: Flash storage is atomic.
 * - Lifecycle: Messages are automatically cleaned up after retrieval.
 * - Type Safety: Uses FlashMessage value object.
 *
 * Usage:
 *   $action = new AddFlash($store);
 *   $action->execute('success', 'Profile updated!', 'success');
 *
 * @package Avax\HTTP\Session\Features\Flash\Actions
 */
final readonly class AddFlash
{
    /**
     * AddFlash Constructor.
     *
     * @param SessionStore $store The session storage backend.
     */
    public function __construct(
        private SessionStore $store
    ) {}

    /**
     * Execute the action: Add flash message.
     *
     * This method:
     * 1. Creates a FlashMessage value object
     * 2. Stores it with flash prefix
     * 3. Tracks the key for cleanup
     *
     * @param string $key   The flash message identifier.
     * @param mixed  $value The message content.
     * @param string $type  The message type (success, error, warning, info).
     *
     * @return void
     */
    public function execute(string $key, mixed $value, string $type = 'info'): void
    {
        // Create flash message value object.
        // This validates the type and key.
        $message = new FlashMessage(
            key: $key,
            value: $value,
            type: $type
        );

        // Store flash message with special prefix.
        $this->store->put(
            key: "_flash.{$key}",
            value: $message->toArray()
        );

        // Track this key for automatic cleanup.
        // Get existing flash keys.
        $flashKeys = $this->store->get(key: '_flash._keys', default: []);

        // Add new key if not already tracked.
        if (!in_array($key, $flashKeys, strict: true)) {
            $flashKeys[] = $key;
            $this->store->put(key: '_flash._keys', value: $flashKeys);
        }

        // Log flash message addition.
        logger()?->debug(
            message: 'Flash message added',
            context: [
                'key' => $key,
                'type' => $type,
                'action' => 'AddFlash',
            ]
        );
    }
}
