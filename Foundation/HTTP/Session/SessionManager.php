<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

use Avax\HTTP\Session\Contracts\BagRegistryInterface;
use Avax\HTTP\Session\Contracts\FlashBagInterface;
use Avax\HTTP\Session\Contracts\SessionBagInterface;
use Avax\HTTP\Session\Contracts\SessionBuilderInterface;
use Avax\HTTP\Session\Contracts\SessionInterface;

/**
 * The `SessionManager` class acts as a central access point for session-related operations.
 *
 * It encapsulates session management logic, adhering to Domain-Driven Design principles, by delegating
 * responsibilities to specialized component contracts such as `SessionInterface` for session state management
 * and `BagRegistryInterface` for organizing session data into modular "bags".
 *
 * Main responsibilities include:
 * - Managing session data using structured namespaces.
 * - Providing access to specialized data containers such as flash messages or error containers.
 * - Abstracting session lifecycle operations and configuration.
 *
 * @internal Immutable class with `readonly` properties ensures integrity and predictable behavior at runtime.
 */
final readonly class SessionManager
{
    /**
     * Constructor.
     *
     * Constructor promotion ensures concise and expressive initialization of immutable properties.
     *
     * @param SessionInterface     $session Core session handler.
     * @param BagRegistryInterface $bags    Bag registry for managing session sub-containers.
     */
    public function __construct(
        private SessionInterface     $session,
        private BagRegistryInterface $bags
    ) {}

    /**
     * Creates a new session builder for a specific namespace.
     *
     * A `SessionBuilder` enables more flexible interaction with session data
     * in a particular structured context. This allows grouping session data
     * logically under a unique namespace.
     *
     * @param string $namespace The namespace for the session context.
     *
     * @return SessionBuilderInterface A session builder for the given namespace.
     */
    public function for(string $namespace) : SessionBuilderInterface
    {
        // Creates a builder with scoped context and namespace.
        return new SessionBuilder(
            session : $this->session,
            registry: $this->bags,
            context : new SessionContext(namespace: $namespace)
        );
    }

    /**
     * Creates a new session builder with the default namespace.
     *
     * This is a shortcut method for working with session data outside of
     * any specific namespace, by default using `default` as the context.
     *
     * @return SessionBuilderInterface A session builder for the default namespace.
     */
    public function builder() : SessionBuilderInterface
    {
        // Creates a builder with the default session namespace.
        return new SessionBuilder(
            session : $this->session,
            registry: $this->bags,
            context : new SessionContext(namespace: 'default')
        );
    }

    /**
     * Stores a key-value pair in the session, ensuring secure handling.
     *
     * Delegates secure, persistent storage to the session handler.
     *
     * @param string $key   The identifier for the session entry.
     * @param mixed  $value The value to associate with the given key.
     *
     * @return void
     */
    public function set(string $key, mixed $value) : void
    {
        $this->session->put(key: $key, value: $value);
    }

    /**
     * Checks if the session contains the given key.
     *
     * @param string $key The session key to check for.
     *
     * @return bool True if the key exists in the session; false otherwise.
     */
    public function has(string $key) : bool
    {
        return $this->session->has(key: $key);
    }

    /**
     * Deletes the specified key from the session.
     *
     * This removes the associated value for the given key from the session storage.
     *
     * @param string $key The identifier of the entry to remove.
     *
     * @return void
     */
    public function delete(string $key) : void
    {
        $this->session->delete(key: $key);
    }

    /**
     * Retrieves the flash message session bag.
     *
     * Flash messages are transient data that persists for only the next request cycle.
     * Commonly used for notifications, feedback messages, or one-time state indicators.
     *
     * @return FlashBagInterface A dedicated flash bag for temporary data.
     */
    public function flash() : FlashBagInterface
    {
        return $this->bags->flash();
    }

    /**
     * Retrieves the error message session bag.
     *
     * An error bag is specifically designed to store validation errors or
     * feedback messages across requests, enabling streamlined error handling for users.
     *
     * @return SessionBagInterface A bag for organizing error-related session data.
     */
    public function errors() : SessionBagInterface
    {
        return $this->bags->errors();
    }

    /**
     * Resets the entire session state.
     *
     * Invalidates the current session and removes all associated data. This is
     * especially useful for logout scenarios or resetting user session contexts.
     *
     * @return void
     */
    public function reset() : void
    {
        $this->session->invalidate();
    }
}