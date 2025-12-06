<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Flash\Actions;

use Avax\HTTP\Session\Features\Flash\FlashMessage;
use Avax\HTTP\Session\Storage\SessionStore;

/**
 * GetFlash Action
 *
 * Single Responsibility: Retrieve and remove a flash message.
 *
 * This action implements the "pull" pattern for flash messages:
 * - Retrieves the message
 * - Removes it from storage (one-time use)
 * - Returns the FlashMessage value object
 *
 * Enterprise Rules:
 * - Atomicity: Retrieval and removal are atomic.
 * - One-time use: Messages are deleted after retrieval.
 * - Type Safety: Returns FlashMessage or null.
 *
 * Usage:
 *   $action = new GetFlash($store);
 *   $message = $action->execute('success');
 *
 * @package Avax\HTTP\Session\Features\Flash\Actions
 */
final readonly class GetFlash
{
    /**
     * GetFlash Constructor.
     *
     * @param SessionStore $store The session storage backend.
     */
    public function __construct(
        private SessionStore $store
    ) {}

    /**
     * Execute the action: Get and remove flash message.
     *
     * This method:
     * 1. Retrieves the flash message data
     * 2. Removes it from storage
     * 3. Returns FlashMessage object or null
     *
     * @param string $key The flash message identifier.
     *
     * @return FlashMessage|null The flash message or null if not found.
     */
    public function execute(string $key): FlashMessage|null
    {
        // Construct the flash storage key.
        $flashKey = "_flash.{$key}";

        // Retrieve the flash message data.
        $data = $this->store->get(key: $flashKey, default: null);

        // If no flash message exists, return null.
        if ($data === null) {
            return null;
        }

        // Remove the flash message from storage (one-time use).
        $this->store->delete(key: $flashKey);

        // Remove key from tracking list.
        $flashKeys = $this->store->get(key: '_flash._keys', default: []);
        $flashKeys = array_filter(
            $flashKeys,
            fn($k) => $k !== $key
        );
        $this->store->put(key: '_flash._keys', value: array_values($flashKeys));

        // Convert array data back to FlashMessage object.
        $message = FlashMessage::fromArray($data);

        // Log flash message retrieval.
        logger()?->debug(
            message: 'Flash message retrieved and removed',
            context: [
                'key' => $key,
                'type' => $message->type,
                'action' => 'GetFlash',
            ]
        );

        return $message;
    }

    /**
     * Peek at a flash message without removing it.
     *
     * Useful for displaying the same flash across multiple requests
     * (though this breaks the flash pattern).
     *
     * @param string $key The flash message identifier.
     *
     * @return FlashMessage|null The flash message or null if not found.
     */
    public function peek(string $key): FlashMessage|null
    {
        // Construct the flash storage key.
        $flashKey = "_flash.{$key}";

        // Retrieve the flash message data without removing.
        $data = $this->store->get(key: $flashKey, default: null);

        // If no flash message exists, return null.
        if ($data === null) {
            return null;
        }

        // Convert array data to FlashMessage object.
        return FlashMessage::fromArray($data);
    }
}
