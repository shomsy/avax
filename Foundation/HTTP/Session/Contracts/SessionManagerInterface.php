<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Contracts;

use Avax\HTTP\Session\SessionBuilder;

/**
 * Interface SessionManagerInterface
 *
 * Defines a clean, type-safe contract for centralized session management.
 * Promotes modularity, testability, and inversion of control.
 */
interface SessionManagerInterface
{
    /**
     * Creates a fluent builder for scoped session access.
     *
     * @return SessionBuilder
     */
    public function builder() : SessionBuilder;

    /**
     * Retrieves a session value by key.
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null) : mixed;

    /**
     * Stores a value securely in the session.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $key, mixed $value) : void;

    /**
     * Determines whether a session key exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key) : bool;

    /**
     * Deletes a session key.
     *
     * @param string $key
     *
     * @return void
     */
    public function delete(string $key) : void;

    /**
     * Resets the session and clears all stored data.
     *
     * @return void
     */
    public function reset() : void;
}
