<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features;

use Avax\HTTP\Session\Contracts\Storage\Store;
use Avax\HTTP\Session\Contracts\FeatureInterface;

/**
 * Flash - Flash Messages Feature
 *
 * Provides one-time messages that persist across a single redirect.
 *
 * Message Types:
 * - success: Success messages
 * - error: Error messages
 * - warning: Warning messages
 * - info: Informational messages
 *
 * @example
 *   $flash->success('Profile updated!');
 *   $flash->error('Invalid credentials');
 *
 *   $message = $flash->get('success');  // Auto-removed after retrieval
 *
 * @package Avax\HTTP\Session
 */
final class Flash implements FeatureInterface
{
    private const PREFIX = '_flash.';
    private bool $enabled = true;

    /**
     * Flash Constructor.
     *
     * @param Store $store The storage backend.
     */
    public function __construct(
        private Store $store
    ) {}

    /**
     * Add a success message.
     *
     * @param string $message The message.
     *
     * @return void
     */
    public function success(string $message): void
    {
        $this->add('success', $message);
    }

    /**
     * Add an error message.
     *
     * @param string $message The message.
     *
     * @return void
     */
    public function error(string $message): void
    {
        $this->add('error', $message);
    }

    /**
     * Add a warning message.
     *
     * @param string $message The message.
     *
     * @return void
     */
    public function warning(string $message): void
    {
        $this->add('warning', $message);
    }

    /**
     * Add an info message.
     *
     * @param string $message The message.
     *
     * @return void
     */
    public function info(string $message): void
    {
        $this->add('info', $message);
    }

    /**
     * Add a flash message.
     *
     * @param string $key     The message key.
     * @param string $message The message.
     *
     * @return void
     */
    public function add(string $key, string $message): void
    {
        $this->store->put(self::PREFIX . $key, $message);
    }

    /**
     * Get and remove a flash message.
     *
     * @param string      $key     The message key.
     * @param string|null $default Default value.
     *
     * @return string|null The message or default.
     */
    public function get(string $key, ?string $default = null): ?string
    {
        $flashKey = self::PREFIX . $key;
        $message = $this->store->get($flashKey, $default);

        if ($message !== $default) {
            $this->store->delete($flashKey);
        }

        return $message;
    }

    /**
     * Check if a flash message exists.
     *
     * @param string $key The message key.
     *
     * @return bool True if exists.
     */
    public function has(string $key): bool
    {
        return $this->store->has(self::PREFIX . $key);
    }

    /**
     * Add a flash message for immediate use (same request).
     *
     * Unlike add(), this message is NOT removed after first retrieval.
     * Use for displaying messages in the same request.
     *
     * @param string $key     The message key.
     * @param string $message The message.
     *
     * @return void
     */
    public function now(string $key, string $message): void
    {
        $this->add("now.{$key}", $message);
    }

    /**
     * Clear all flash messages.
     *
     * @return void
     */
    public function clear(): void
    {
        $all = $this->store->all();

        foreach (array_keys($all) as $key) {
            if (str_starts_with($key, self::PREFIX)) {
                $this->store->delete($key);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        // Flash messages are lazy-loaded, no boot logic needed
        $this->enabled = true;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(): void
    {
        // Clear all flash messages on session termination
        $this->clear();
        $this->enabled = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'flash';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
