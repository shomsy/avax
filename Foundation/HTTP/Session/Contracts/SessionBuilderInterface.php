<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Contracts;

use ArrayAccess;

/**
 * Interface SessionBuilderInterface
 *
 * Represents the contract for a fluent, Domain-Specific Language (DSL)-oriented
 * interface for manipulating session data in a contextual and flexible manner.
 *
 * Design principles:
 * - Implements immutable fluency for operations when applicable.
 * - Encourages brevity and readability during session management via fluent chaining.
 *
 * Extends:
 * - ArrayAccess: Enables idiomatic usage of array-like syntax,
 *   allowing operations like:
 *      - `$builder['key']`: Direct access to session data.
 *      - `isset($builder['key'])`: Check presence of session keys.
 *      - `unset($builder['key'])`: Deleting session keys.
 */
interface SessionBuilderInterface extends ArrayAccess
{
    /**
     * Retrieves a value from the session using a specified key, with support for
     * an optional fallback default.
     *
     * @param string     $key     The unique identifier for the value within the session.
     * @param mixed|null $default A default value to return if the key is not found (optional).
     *
     * @return mixed The value associated with the specified key, or the default value if not present.
     */
    public function get(string $key, mixed $default = null) : mixed;

    /**
     * Stores a value in the session under the specified key.
     *
     * @param string $key   The unique identifier for the value within the session.
     * @param mixed  $value The value to store in the session.
     *
     * @return void No return value; modifies session state directly.
     */
    public function set(string $key, mixed $value) : void;

    /**
     * Checks if a given key exists in the session.
     *
     * @param string $key The unique identifier to check for existence in the session.
     *
     * @return bool True if the key exists, False otherwise.
     */
    public function has(string $key) : bool;

    /**
     * Deletes a value from the session using the specified key.
     *
     * @param string $key The unique identifier of the value to delete from the session.
     *
     * @return void No return value; removes data from session.
     */
    public function delete(string $key) : void;

    /**
     * Sets the current namespace for session operations, enabling segmentation
     * or scoped session values.
     *
     * @param string $namespace The namespace to apply for subsequent session operations.
     *
     * @return self Returns the instance of the session builder for fluent operations.
     */
    public function withNamespace(string $namespace) : self;

    /**
     * Defines the Time-To-Live (TTL) duration for session data, allowing specification
     * of expiry in seconds.
     *
     * @param int $seconds The time (in seconds) before the session data is marked as expired.
     *
     * @return self Returns the instance of the session builder for fluent chaining.
     */
    public function withTTL(int $seconds) : self;

    /**
     * Marks the session as "secure," ensuring that session data adheres to stricter
     * security constraints, such as automatically using HTTPS or encryption policies.
     *
     * @return self Returns the instance of the session builder for fluent chaining.
     */
    public function secure() : self;

    /**
     * Magic invocation method to retrieve session data. Acts as a shorthand for retrieving
     * a key with an optional default value directly.
     *
     * @param string     $key     The unique identifier for the value.
     * @param mixed|null $default The fallback default value (optional).
     *
     * @return mixed The value associated with the key, or the default value if not set.
     */
    public function __invoke(string $key, mixed $default = null) : mixed;

    /**
     * Serializes the current session builder state to a string representation. Typically used for
     * debugging or interoperability with systems expecting a string output.
     *
     * @return string A string representation of the session builder's current state.
     */
    public function __toString() : string;
}