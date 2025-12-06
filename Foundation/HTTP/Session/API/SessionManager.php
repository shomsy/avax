<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\API;

use Avax\HTTP\Session\Actions\InvalidateSession;
use Avax\HTTP\Session\Actions\RegenerateId;
use Avax\HTTP\Session\Actions\StartSession;
use Avax\HTTP\Session\Core\SessionContext;
use Avax\HTTP\Session\Features\Flash\FlashBag;
use Avax\HTTP\Session\Storage\SessionStore;
use Closure;

/**
 * SessionManager
 *
 * Main entry point for all session operations.
 *
 * This facade provides a clean, fluent DSL for session management,
 * orchestrating Actions and Features to deliver intuitive developer
 * experience.
 *
 * Enterprise Rules:
 * - Facade Pattern: Simplifies complex subsystem interactions.
 * - DSL Design: Natural language method names.
 * - Delegation: Delegates to Actions for testability.
 *
 * Usage:
 *   Session::scope('cart')->store('items', [1, 2, 3]);
 *   Session::flash('success', 'Saved!');
 *   Session::temporary(300)->store('otp', '123456');
 *
 * @package Avax\HTTP\Session\API
 */
final readonly class SessionManager
{
    /**
     * SessionManager Constructor.
     *
     * @param SessionStore $store The session storage backend.
     * @param FlashBag     $flash The flash messages feature.
     */
    public function __construct(
        private SessionStore $store,
        private FlashBag $flash
    ) {}

    /**
     * Start the session.
     *
     * Initializes the session lifecycle. Safe to call multiple times.
     *
     * @return self For method chaining.
     */
    public function start(): self
    {
        // Delegate to StartSession action.
        $action = new StartSession($this->store);
        $action->execute();

        return $this;
    }

    /**
     * Create a scoped session context.
     *
     * Enables namespace isolation for session data.
     *
     * Example:
     *   Session::scope('cart')->store('items', [1, 2, 3]);
     *
     * @param string $namespace The namespace identifier.
     *
     * @return FluentSession Fluent builder for scoped operations.
     */
    public function scope(string $namespace): FluentSession
    {
        // Create context for the namespace.
        $context = SessionContext::for($namespace);

        // Return fluent builder.
        return new FluentSession(
            store: $this->store,
            context: $context
        );
    }

    /**
     * Alias for scope() - more natural English.
     *
     * Example:
     *   Session::in('checkout')->store('payment', $data);
     *
     * @param string $namespace The namespace identifier.
     *
     * @return FluentSession Fluent builder for scoped operations.
     */
    public function in(string $namespace): FluentSession
    {
        return $this->scope($namespace);
    }

    /**
     * Create a temporary session with TTL.
     *
     * Values stored in this context will expire after the specified duration.
     *
     * Example:
     *   Session::temporary(300)->store('otp', '123456');
     *
     * @param int $seconds Time-to-live in seconds.
     *
     * @return FluentSession Fluent builder with TTL.
     */
    public function temporary(int $seconds): FluentSession
    {
        // Create default context with TTL.
        $context = SessionContext::default()->withTTL($seconds);

        // Return fluent builder.
        return new FluentSession(
            store: $this->store,
            context: $context
        );
    }

    /**
     * Get the default fluent builder.
     *
     * Example:
     *   Session::builder()->encrypt()->store('token', $value);
     *
     * @return FluentSession Fluent builder with default context.
     */
    public function builder(): FluentSession
    {
        // Create default context.
        $context = SessionContext::default();

        // Return fluent builder.
        return new FluentSession(
            store: $this->store,
            context: $context
        );
    }

    /**
     * Quick flash message.
     *
     * Convenience method for adding flash messages.
     *
     * Example:
     *   Session::flash('success', 'Profile updated!');
     *
     * @param string $key   The flash message identifier.
     * @param mixed  $value The message content.
     * @param string $type  The message type (success, error, warning, info).
     *
     * @return void
     */
    public function flash(string $key, mixed $value, string $type = 'info'): void
    {
        // Delegate to FlashBag.
        $this->flash->add(key: $key, value: $value, type: $type);
    }

    /**
     * Access the flash bag.
     *
     * Example:
     *   Session::flashBag()->success('message', 'Saved!');
     *
     * @return FlashBag The flash messages feature.
     */
    public function flashBag(): FlashBag
    {
        return $this->flash;
    }

    /**
     * Remember pattern - lazy evaluation.
     *
     * Retrieves a value or executes a callback to generate and store it.
     *
     * Example:
     *   $user = Session::remember('current_user', fn() => User::find($id));
     *
     * @param string  $key      The session key.
     * @param Closure $callback The callback to generate the value.
     *
     * @return mixed The retrieved or generated value.
     */
    public function remember(string $key, Closure $callback): mixed
    {
        // Check if value exists.
        if ($this->store->has($key)) {
            return $this->store->get($key);
        }

        // Execute callback to generate value.
        $value = $callback();

        // Store the generated value.
        $this->store->put(key: $key, value: $value);

        // Return the value.
        return $value;
    }

    /**
     * Store a value in the default namespace.
     *
     * Example:
     *   Session::put('user_id', 123);
     *
     * @param string $key   The session key.
     * @param mixed  $value The value to store.
     *
     * @return void
     */
    public function put(string $key, mixed $value): void
    {
        $this->store->put(key: $key, value: $value);
    }

    /**
     * Retrieve a value from the default namespace.
     *
     * Example:
     *   $userId = Session::get('user_id', default: null);
     *
     * @param string $key     The session key.
     * @param mixed  $default The default value.
     *
     * @return mixed The retrieved value or default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store->get(key: $key, default: $default);
    }

    /**
     * Check if a key exists in the default namespace.
     *
     * Example:
     *   if (Session::has('user_id')) { ... }
     *
     * @param string $key The session key.
     *
     * @return bool True if key exists.
     */
    public function has(string $key): bool
    {
        return $this->store->has(key: $key);
    }

    /**
     * Delete a value from the default namespace.
     *
     * Example:
     *   Session::delete('user_id');
     *
     * @param string $key The session key.
     *
     * @return void
     */
    public function delete(string $key): void
    {
        $this->store->delete(key: $key);
    }

    /**
     * Get all session data.
     *
     * Example:
     *   $allData = Session::all();
     *
     * @return array<string, mixed> All session data.
     */
    public function all(): array
    {
        return $this->store->all();
    }

    /**
     * Invalidate and destroy the session.
     *
     * This is a security-critical operation that clears all data
     * and regenerates the session ID.
     *   Session::regenerate(); // After login
     *
     * @param bool $deleteOldSession Whether to destroy old session data.
     *
     * @return void
     */
    public function regenerate(bool $deleteOldSession = true): void
    {
        // Delegate to RegenerateId action.
        $action = new RegenerateId($this->store);
        $action->execute(deleteOldSession: $deleteOldSession);
    }

    /**
     * Get the current session ID.
     *
     * @return string The session identifier.
     */
    public function id(): string
    {
        return $this->store->getId();
    }
}
