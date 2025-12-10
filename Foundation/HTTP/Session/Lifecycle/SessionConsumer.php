<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Lifecycle;

/**
 * Session Component
 *
 * SessionConsumer - Contextual Session Consumer
 *
 * Represents a contextual, purpose-specific consumer of the SessionProvider.
 * Created via $session->for($context) or $session->scope($context).
 *
 * Provider-Consumer Pattern:
 * - SessionProvider = Provider (aggregate root, lifecycle management)
 * - SessionConsumer = Consumer (contextual DSL adapter)
 *
 * Features:
 * - Namespace isolation (e.g., 'cart', 'user', 'admin')
 * - TTL configuration
 * - Auto-encryption via secure() method
 * - Chainable fluent API
 *
 * @example
 *   $session->for('cart')
 *       ->secure()
 *       ->ttl(3600)
 *       ->put('items', $items);
 *
 * @author  Milos Stankovic
 * @package Shomsy\Components\Foundation\HTTP\Session\Lifecycle
 */
final class SessionConsumer
{
    private int|null $ttl    = null;
    private bool     $secure = false;

    /**
     * SessionConsumer Constructor.
     *
     * @param string          $namespace The consumer context namespace.
     * @param SessionProvider $provider  The session provider.
     */
    public function __construct(
        private readonly string          $namespace,
        private readonly SessionProvider $provider
    ) {}

    /**
     * Enable auto-encryption for all operations in this consumer context.
     *
     * @return self Fluent interface.
     */
    public function secure() : self
    {
        $this->secure = true;

        return $this;
    }

    /**
     * Store a value in this consumer context.
     *
     * @param string $key   The key (will be namespaced).
     * @param mixed  $value The value to store.
     *
     * @return void
     */
    public function put(string $key, mixed $value) : void
    {
        $scopedKey = $this->buildKey($key);
        $this->provider->put(
            key  : $scopedKey,
            value: $value,
        );
    }

    /**
     * Build scoped key with namespace and security suffix.
     *
     * @param string $key The base key.
     *
     * @return string The scoped key.
     */
    private function buildKey(string $key) : string
    {
        $scopedKey = "{$this->namespace}.{$key}";

        if ($this->secure) {
            $scopedKey .= '_secure';
        }

        return $scopedKey;
    }

    /**
     * Retrieve a value from this consumer context.
     *
     * @param string $key     The key (will be namespaced).
     * @param mixed  $default Default value if not found.
     *
     * @return mixed The retrieved value or default.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        $scopedKey = $this->buildKey($key);

        return $this->provider->get(
            key    : $scopedKey,
            default: $default
        );
    }

    /**
     * Check if a key exists in this consumer context.
     *
     * @param string $key The key (will be namespaced).
     *
     * @return bool True if key exists.
     */
    public function has(string $key) : bool
    {
        $scopedKey = $this->buildKey($key);

        return $this->provider->has(key: $scopedKey);
    }

    /**
     * Remove a value from this consumer context.
     *
     * @param string $key The key (will be namespaced).
     *
     * @return void
     */
    public function forget(string $key) : void
    {
        $scopedKey = $this->buildKey($key);
        $this->provider->forget(key: $scopedKey);
    }

    /**
     * Remember a value using lazy evaluation.
     *
     * Proxy to provider's remember() method with scoped key.
     *
     * @param string   $key      The key (will be namespaced).
     * @param callable $callback Callback to generate value.
     *
     * @return mixed The cached or generated value.
     */
    public function remember(string $key, callable $callback) : mixed
    {
        $scopedKey = $this->buildKey($key);

        return $this->provider->remember(
            key     : $scopedKey,
            callback: $callback,
            ttl     : $this->ttl
        );
    }

    /**
     * Create a temporary consumer with TTL.
     *
     * Shortcut for ->ttl() configuration.
     *
     * @param int $seconds Time-to-live in seconds.
     *
     * @return self Consumer with TTL configured.
     */
    public function temporary(int $seconds) : self
    {
        return $this->ttl($seconds);
    }

    /**
     * Set TTL for all operations in this consumer context.
     *
     * @param int $seconds Time-to-live in seconds.
     *
     * @return self Fluent interface.
     */
    public function ttl(int $seconds) : self
    {
        $this->ttl = $seconds;

        return $this;
    }

    /**
     * String representation for debugging.
     *
     * @return string Debug representation.
     */
    public function __toString() : string
    {
        return sprintf(
            'SessionConsumer(%s, secure=%s, ttl=%s)',
            $this->namespace,
            $this->secure ? 'true' : 'false',
            $this->ttl ?? 'null'
        );
    }
}
