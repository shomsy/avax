<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core\Lifecycle;

/**
 * SessionScope - Contextual Session Consumer
 *
 * Represents a contextual, purpose-specific consumer of the SessionEngine.
 * Created via $session->for($context) or $session->scope($context).
 *
 * engine-Consumer Pattern:
 * - SessionEngine = engine (aggregate root, lifecycle management)
 * - SessionScope = Consumer (contextual DSL adapter)
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
 */
final class SessionScope
{
    private int|null $ttl = null;

    private bool $secure = false;

    /**
     * SessionScope Constructor.
     *
     * @param string        $namespace The consumer context namespace.
     * @param SessionEngine $engine    The session engine.
     */
    public function __construct(
        private readonly string        $namespace,
        private readonly SessionEngine $engine
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
     * Remove a value from this consumer context.
     *
     * @param string $key The key (will be namespaced).
     */
    public function forget(string $key) : void
    {
        $scopedKey = $this->buildKey(key: $key);
        $this->engine->storage()->delete(key: $scopedKey);
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
     * Remember a value using lazy evaluation.
     *
     * Proxy to engine's get/put methods with scoped key.
     *
     * @param string   $key      The key (will be namespaced).
     * @param callable $callback Callback to generate value.
     *
     * @return mixed The cached or generated value.
     */
    public function remember(string $key, callable $callback) : mixed
    {
        $scopedKey = $this->buildKey(key: $key);

        if ($this->engine->storage()->has(key: $scopedKey)) {
            return $this->engine->get(key: $scopedKey);
        }

        $value = $callback();
        $this->engine->put(key: $scopedKey, value: $value);

        return $value;
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
        $scopedKey = $this->buildKey(key: $key);

        return $this->engine->storage()->has(key: $scopedKey);
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
        $scopedKey = $this->buildKey(key: $key);

        return $this->engine->get(
            key    : $scopedKey,
            default: $default
        );
    }

    /**
     * Store a value in this consumer context.
     *
     * @param string $key   The key (will be namespaced).
     * @param mixed  $value The value to store.
     */
    public function put(string $key, mixed $value) : void
    {
        $scopedKey = $this->buildKey(key: $key);
        $this->engine->put(
            key  : $scopedKey,
            value: $value,
            ttl  : $this->ttl
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
        return $this->ttl(seconds: $seconds);
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
            'SessionScope(%s, secure=%s, ttl=%s)',
            $this->namespace,
            $this->secure ? 'true' : 'false',
            $this->ttl ?? 'null'
        );
    }
}
