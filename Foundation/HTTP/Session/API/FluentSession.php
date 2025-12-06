<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\API;

use Avax\HTTP\Session\Actions\RetrieveValue;
use Avax\HTTP\Session\Actions\StoreValue;
use Avax\HTTP\Session\Core\SessionContext;
use Avax\HTTP\Session\Features\Crypto\Actions\DecryptValue;
use Avax\HTTP\Session\Features\Crypto\Actions\EncryptValue;
use Avax\HTTP\Session\Features\TTL\Actions\CheckExpiration;
use Avax\HTTP\Session\Features\TTL\Actions\SetTTL;
use Avax\HTTP\Session\Storage\SessionStore;
use Avax\Security\Encryption\Contracts\EncrypterInterface;
use Closure;
use Stringable;

/**
 * FluentSession
 *
 * Fluent DSL builder for contextual session operations.
 *
 * This builder provides an immutable, chainable API for configuring
 * and executing session operations with various policies (encryption,
 * TTL, namespace isolation).
 *
 * Enterprise Rules:
 * - Immutability: Each method returns a new instance.
 * - Fluent API: Natural language method chaining.
 * - Context Preservation: Configuration is carried through the chain.
 *
 * Usage:
 *   Session::scope('cart')
 *       ->encrypt()
 *       ->withTTL(3600)
 *       ->store('items', [1, 2, 3]);
 *
 * @package Avax\HTTP\Session\API
 */
final readonly class FluentSession implements Stringable
{
    /**
     * FluentSession Constructor.
     *
     * @param SessionStore   $store   The session storage backend.
     * @param SessionContext $context The contextual configuration.
     */
    public function __construct(
        private SessionStore $store,
        private SessionContext $context
    ) {}

    /**
     * Enable encryption for stored values.
     *
     * Values stored after calling this method will be encrypted.
     *
     * Example:
     *   Session::builder()->encrypt()->store('token', $apiToken);
     *
     * @return self New immutable instance with encryption enabled.
     */
    public function encrypt(): self
    {
        // Clone context with encryption enabled.
        return new self(
            store: $this->store,
            context: $this->context->secure()
        );
    }

    /**
     * Set Time-To-Live for stored values.
     *
     * Values stored after calling this method will expire after the
     * specified duration.
     *
     * Example:
     *   Session::builder()->withTTL(300)->store('otp', '123456');
     *
     * @param int $seconds Time-to-live in seconds.
     *
     * @return self New immutable instance with TTL set.
     */
    public function withTTL(int $seconds): self
    {
        // Clone context with TTL set.
        return new self(
            store: $this->store,
            context: $this->context->withTTL($seconds)
        );
    }

    /**
     * Switch to a different namespace.
     *
     * Example:
     *   Session::builder()->namespace('admin')->store('role', 'superuser');
     *
     * @param string $namespace The namespace identifier.
     *
     * @return self New immutable instance with different namespace.
     */
    public function namespace(string $namespace): self
    {
        // Clone context with new namespace.
        return new self(
            store: $this->store,
            context: $this->context->forNamespace($namespace)
        );
    }

    /**
     * Store a value with the current context.
     *
     * This method applies all configured policies (encryption, TTL, namespace).
     *
     * Example:
     *   Session::scope('cart')->store('items', [1, 2, 3]);
     *
     * @param string $key   The session key.
     * @param mixed  $value The value to store.
     *
     * @return void
     */
    public function store(string $key, mixed $value): void
    {
        // Get encrypter from container.
        $encrypter = app(EncrypterInterface::class);

        // Create actions.
        $encryptAction = new EncryptValue($encrypter);
        $ttlAction = new SetTTL($this->store);

        // Delegate to StoreValue action.
        $action = new StoreValue(
            store: $this->store,
            context: $this->context,
            encryptor: $encryptAction,
            ttlSetter: $ttlAction
        );

        $action->execute(key: $key, value: $value);
    }

    /**
     * Retrieve a value with the current context.
     *
     * This method handles decryption and TTL validation automatically.
     *
     * Example:
     *   $items = Session::scope('cart')->retrieve('items', default: []);
     *
     * @param string $key     The session key.
     * @param mixed  $default The default value.
     *
     * @return mixed The retrieved value or default.
     */
    public function retrieve(string $key, mixed $default = null): mixed
    {
        // Get encrypter from container.
        $encrypter = app(EncrypterInterface::class);

        // Create actions.
        $decryptAction = new DecryptValue($encrypter);
        $ttlChecker = new CheckExpiration($this->store);

        // Delegate to RetrieveValue action.
        $action = new RetrieveValue(
            store: $this->store,
            context: $this->context,
            decryptor: $decryptAction,
            ttlChecker: $ttlChecker
        );

        return $action->execute(key: $key, default: $default);
    }

    /**
     * Check if a key exists in the current context.
     *
     * Example:
     *   if (Session::scope('cart')->has('items')) { ... }
     *
     * @param string $key The session key.
     *
     * @return bool True if key exists.
     */
    public function has(string $key): bool
    {
        // Resolve namespaced key.
        $resolvedKey = $this->resolveKey($key);

        return $this->store->has(key: $resolvedKey);
    }

    /**
     * Delete a value from the current context.
     *
     * Example:
     *   Session::scope('cart')->delete('items');
     *
     * @param string $key The session key.
     *
     * @return void
     */
    public function delete(string $key): void
    {
        // Resolve namespaced key.
        $resolvedKey = $this->resolveKey($key);

        $this->store->delete(key: $resolvedKey);
    }

    /**
     * Remember pattern - lazy evaluation.
     *
     * Retrieves a value or executes a callback to generate and store it.
     *
     * Example:
     *   $user = Session::scope('auth')->remember('user', fn() => User::find($id));
     *
     * @param string  $key      The session key.
     * @param Closure $callback The callback to generate the value.
     *
     * @return mixed The retrieved or generated value.
     */
    public function remember(string $key, Closure $callback): mixed
    {
        // Check if value exists.
        if ($this->has($key)) {
            return $this->retrieve($key);
        }

        // Execute callback to generate value.
        $value = $callback();

        // Store the generated value.
        $this->store(key: $key, value: $value);

        // Return the value.
        return $value;
    }

    /**
     * Magic invoke for quick retrieval.
     *
     * Example:
     *   $items = Session::scope('cart')('items', default: []);
     *
     * @param string $key     The session key.
     * @param mixed  $default The default value.
     *
     * @return mixed The retrieved value or default.
     */
    public function __invoke(string $key, mixed $default = null): mixed
    {
        return $this->retrieve(key: $key, default: $default);
    }

    /**
     * String representation for debugging.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->context;
    }

    /**
     * Resolve the full key with namespace prefix.
     *
     * @param string $key The base key.
     *
     * @return string The fully qualified key.
     */
    private function resolveKey(string $key): string
    {
        // Return raw key if no namespace or default namespace.
        if ($this->context->namespace === '' || $this->context->namespace === 'default') {
            return $key;
        }

        // Return namespaced key.
        return "{$this->context->namespace}.{$key}";
    }
}
