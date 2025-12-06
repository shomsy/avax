<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core;

use Avax\HTTP\Session\Storage\SessionStore;

/**
 * BaseSession
 *
 * Pure session lifecycle management.
 *
 * This class contains ONLY core session operations:
 * - start, set, get, delete, flush, id
 *
 * All behaviors (TTL, Crypto, Flash) are extracted to separate modules.
 *
 * Enterprise Rules:
 * - Single Responsibility: Only lifecycle management
 * - No business logic: Delegates to features
 * - Pure DI: All dependencies injected
 *
 * @package Avax\HTTP\Session\Core
 */
class BaseSession
{
    /**
     * BaseSession Constructor.
     *
     * @param SessionStore $store The storage backend.
     */
    public function __construct(
        protected readonly SessionStore $store
    ) {}

    /**
     * Start the session.
     *
     * @return void
     */
    public function start(): void
    {
        $this->store->start();
    }

    /**
     * Check if session is started.
     *
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->store->isStarted();
    }

    /**
     * Get session ID.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->store->getId();
    }

    /**
     * Set a value.
     *
     * @param string $key   The key.
     * @param mixed  $value The value.
     *
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->store->put($key, $value);
    }

    /**
     * Get a value.
     *
     * @param string $key     The key.
     * @param mixed  $default Default value.
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store->get($key, $default);
    }

    /**
     * Check if key exists.
     *
     * @param string $key The key.
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->store->has($key);
    }

    /**
     * Delete a value.
     *
     * @param string $key The key.
     *
     * @return void
     */
    public function delete(string $key): void
    {
        $this->store->delete($key);
    }

    /**
     * Get all data.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->store->all();
    }

    /**
     * Flush all data.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->store->flush();
    }

    /**
     * Regenerate session ID.
     *
     * @param bool $deleteOldSession Whether to delete old session.
     *
     * @return void
     */
    public function regenerateId(bool $deleteOldSession = true): void
    {
        $this->store->regenerateId($deleteOldSession);
    }
}
