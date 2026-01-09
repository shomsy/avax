<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Traits;

use Avax\HTTP\Session\Shared\Contracts\SessionInterface;

/**
 * Trait that provides session management capabilities.
 *
 * This trait assumes that a concrete implementation of SessionInterface
 * is provided and assigned to the $session property. This allows for
 * centralized session management within any class that uses this trait.
 */
trait SessionManagementTrait
{
    protected SessionInterface $session;

    /**
     * Returns the session instance.
     *
     * This method allows access to the underlying session object, which
     * might be required for more advanced operations beyond the provided
     * methods in this trait.
     */
    public function session() : SessionInterface
    {
        return $this->session;
    }

    /**
     * Retrieves a value from the session.
     *
     * Provides a default value if the key does not exist in the session.
     *
     * @param string $key     The session key to retrieve.
     * @param mixed  $default The default value to return if key doesn't exist (default: null).
     *
     * @return mixed The value stored in session or the default value.
     */
    public function getSessionValue(string $key, mixed $default = null) : mixed
    {
        return $this->session->has(key: $key) ? $this->session->get(key: $key) : $default;
    }

    /**
     * Sets a value in the session.
     *
     * This method assigns a given value to a specified session key.
     *
     * @param string $key   The session key where the value should be stored.
     * @param mixed  $value The value to store in session.
     */
    public function setSessionValue(string $key, mixed $value) : void
    {
        $this->session->set(key: $key, value: $value);
    }

    /**
     * Checks if a session key exists.
     *
     * This method returns true if the specified key is present in the session.
     *
     * @param string $key The session key to check for existence.
     *
     * @return bool True if the key exists in the session, false otherwise.
     */
    public function hasSessionValue(string $key) : bool
    {
        return $this->session->has(key: $key);
    }

    /**
     * Retrieves a flashed value from the session.
     *
     * Flash data is meant for short-lived session data, commonly used for
     * notifications that only need to survive for the next request.
     *
     * @param string $key     The session key for the flash value.
     * @param mixed  $default The default value to return if key doesn't exist (default: null).
     *
     * @return mixed The flashed value or the default value.
     */
    public function getFlash(string $key, mixed $default = null) : mixed
    {
        return $this->session->flash()->get(key: $key, default: $default) ?? $default;
    }

    /**
     * Retrieves the current user from the session.
     *
     * This method assumes that user data is stored under the 'user' key.
     * Useful for accessing currently authenticated user information.
     *
     * @return mixed The user object or data stored in session, or `null` if not set.
     */
    public function user() : mixed
    {
        return $this->session->get(key: 'user');
    }

    /**
     * Stores the current user in the session.
     *
     * This method assigns a user object or data to the 'user' key in the session.
     *
     * @param mixed $user The user object or data to store.
     */
    public function setUser(mixed $user) : void
    {
        $this->session->set(key: 'user', value: $user);
    }
}

