<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Flash;

use Avax\HTTP\Session\Features\Flash\Actions\AddFlash;
use Avax\HTTP\Session\Features\Flash\Actions\GetFlash;
use Avax\HTTP\Session\Storage\SessionStore;

/**
 * FlashBag
 *
 * Manages flash messages following the Post-Redirect-Get pattern.
 *
 * Flash messages are temporary notifications that persist for exactly one
 * request cycle, making them perfect for displaying feedback after form
 * submissions or redirects.
 *
 * Enterprise Rules:
 * - One-time use: Messages are consumed on retrieval.
 * - Type safety: Uses FlashMessage value objects.
 * - Composability: Delegates to Actions for testability.
 *
 * Usage:
 *   $flash = new FlashBag($store);
 *   $flash->add('success', 'Profile updated!');
 *   $message = $flash->get('success');
 *
 * @package Avax\HTTP\Session\Features\Flash
 */
final readonly class FlashBag
{
    /**
     * FlashBag Constructor.
     *
     * @param SessionStore $store The session storage backend.
     */
    public function __construct(
        private SessionStore $store
    ) {}

    /**
     * Add a flash message.
     *
     * @param string $key   The flash message identifier.
     * @param mixed  $value The message content.
     * @param string $type  The message type (success, error, warning, info).
     *
     * @return void
     */
    public function add(string $key, mixed $value, string $type = 'info'): void
    {
        // Delegate to AddFlash action.
        $action = new AddFlash($this->store);
        $action->execute(key: $key, value: $value, type: $type);
    }

    /**
     * Retrieve and remove a flash message.
     *
     * @param string $key The flash message identifier.
     *
     * @return FlashMessage|null The flash message or null if not found.
     */
    public function get(string $key): FlashMessage|null
    {
        // Delegate to GetFlash action.
        $action = new GetFlash($this->store);
        return $action->execute(key: $key);
    }

    /**
     * Peek at a flash message without removing it.
     *
     * @param string $key The flash message identifier.
     *
     * @return FlashMessage|null The flash message or null if not found.
     */
    public function peek(string $key): FlashMessage|null
    {
        // Delegate to GetFlash action.
        $action = new GetFlash($this->store);
        return $action->peek(key: $key);
    }

    /**
     * Check if a flash message exists.
     *
     * @param string $key The flash message identifier.
     *
     * @return bool True if the flash message exists.
     */
    public function has(string $key): bool
    {
        return $this->store->has(key: "_flash.{$key}");
    }

    /**
     * Get all flash messages and remove them.
     *
     * @return array<string, FlashMessage> Array of flash messages keyed by identifier.
     */
    public function all(): array
    {
        // Get all tracked flash keys.
        $flashKeys = $this->store->get(key: '_flash._keys', default: []);

        // Retrieve all flash messages.
        $messages = [];
        foreach ($flashKeys as $key) {
            $message = $this->get($key);
            if ($message !== null) {
                $messages[$key] = $message;
            }
        }

        return $messages;
    }

    /**
     * Clear all flash messages.
     *
     * @return void
     */
    public function clear(): void
    {
        // Get all tracked flash keys.
        $flashKeys = $this->store->get(key: '_flash._keys', default: []);

        // Delete each flash message.
        foreach ($flashKeys as $key) {
            $this->store->delete(key: "_flash.{$key}");
        }

        // Clear the tracking list.
        $this->store->delete(key: '_flash._keys');
    }

    /**
     * Convenience method: Add success message.
     *
     * @param string $key   The flash message identifier.
     * @param mixed  $value The message content.
     *
     * @return void
     */
    public function success(string $key, mixed $value): void
    {
        $this->add(key: $key, value: $value, type: 'success');
    }

    /**
     * Convenience method: Add error message.
     *
     * @param string $key   The flash message identifier.
     * @param mixed  $value The message content.
     *
     * @return void
     */
    public function error(string $key, mixed $value): void
    {
        $this->add(key: $key, value: $value, type: 'error');
    }

    /**
     * Convenience method: Add warning message.
     *
     * @param string $key   The flash message identifier.
     * @param mixed  $value The message content.
     *
     * @return void
     */
    public function warning(string $key, mixed $value): void
    {
        $this->add(key: $key, value: $value, type: 'warning');
    }

    /**
     * Convenience method: Add info message.
     *
     * @param string $key   The flash message identifier.
     * @param mixed  $value The message content.
     *
     * @return void
     */
    public function info(string $key, mixed $value): void
    {
        $this->add(key: $key, value: $value, type: 'info');
    }
}
