<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Storage;

use Avax\HTTP\Session\Support\SessionIdGenerator;

/**
 * ArrayStore
 *
 * In-memory session storage implementation.
 *
 * This store keeps all session data in memory (PHP array), making it
 * perfect for testing and development. Data does NOT persist across requests.
 *
 * Enterprise Rules:
 * - Testing-focused: Ideal for unit tests.
 * - No persistence: Data lost after request ends.
 * - Fast: No I/O operations.
 *
 * Usage:
 *   $store = new ArrayStore();
 *   $store->start();
 *   $store->put('key', 'value');
 *
 * @package Avax\HTTP\Session\Storage
 */
final class ArrayStore implements SessionStore
{
    /**
     * In-memory session data.
     *
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * Session started flag.
     *
     * @var bool
     */
    private bool $started = false;

    /**
     * Session ID.
     *
     * @var string|null
     */
    private string|null $sessionId = null;

    /**
     * Session ID generator.
     *
     * @var SessionIdGenerator
     */
    private readonly SessionIdGenerator $idGenerator;

    /**
     * ArrayStore Constructor.
     */
    public function __construct()
    {
        // Initialize ID generator.
        $this->idGenerator = new SessionIdGenerator();
    }

    /**
     * Start the session.
     *
     * @return void
     */
    public function start(): void
    {
        // Mark as started if not already.
        if (!$this->started) {
            $this->started = true;

            // Generate session ID if not set.
            if ($this->sessionId === null) {
                $this->sessionId = $this->idGenerator->generate();
            }
        }
    }

    /**
     * Check if session is started.
     *
     * @return bool True if session is active.
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Get the current session ID.
     *
     * @return string The session identifier.
     */
    public function getId(): string
    {
        // Ensure session is started.
        $this->start();

        // Return session ID.
        return $this->sessionId ?? '';
    }

    /**
     * Regenerate the session ID.
     *
     * @param bool $deleteOldSession Whether to destroy old session data.
     *
     * @return void
     */
    public function regenerateId(bool $deleteOldSession = true): void
    {
        // Ensure session is started.
        $this->start();

        // Generate new session ID.
        $this->sessionId = $this->idGenerator->generate();

        // Optionally clear data.
        if ($deleteOldSession) {
            $this->flush();
        }
    }

    /**
     * Store a value in the session.
     *
     * @param string $key   The storage key.
     * @param mixed  $value The value to store.
     *
     * @return void
     */
    public function put(string $key, mixed $value): void
    {
        // Ensure session is started.
        $this->start();

        // Store in memory.
        $this->data[$key] = $value;
    }

    /**
     * Retrieve a value from the session.
     *
     * @param string $key     The storage key.
     * @param mixed  $default The default value.
     *
     * @return mixed The stored value or default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Ensure session is started.
        $this->start();

        // Return value or default.
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if a key exists.
     *
     * @param string $key The storage key.
     *
     * @return bool True if key exists.
     */
    public function has(string $key): bool
    {
        // Ensure session is started.
        $this->start();

        // Check if key exists in data.
        return array_key_exists($key, $this->data);
    }

    /**
     * Delete a value from the session.
     *
     * @param string $key The storage key.
     *
     * @return void
     */
    public function delete(string $key): void
    {
        // Ensure session is started.
        $this->start();

        // Remove from memory.
        unset($this->data[$key]);
    }

    /**
     * Get all session data.
     *
     * @return array<string, mixed> All session data.
     */
    public function all(): array
    {
        // Ensure session is started.
        $this->start();

        // Return all data.
        return $this->data;
    }

    /**
     * Clear all session data.
     *
     * @return void
     */
    public function flush(): void
    {
        // Ensure session is started.
        $this->start();

        // Empty data array.
        $this->data = [];
    }
}
