<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session;

use Gemini\HTTP\Session\Contracts\BagRegistryInterface;
use Gemini\HTTP\Session\Contracts\SessionBuilderInterface;
use Gemini\HTTP\Session\Contracts\SessionInterface;
use Gemini\HTTP\Session\Enums\SessionTag;
use Override;

/**
 * Fluent DSL (Domain-specific language) for building contextual session operations.
 *
 * This `SessionBuilder` class enables advanced session management capabilities.
 * It provides support for:
 * - Namespacing: To logically separate session data into scoped groups.
 * - Time-To-Live (TTL): To specify expiration durations for session values.
 * - Secure Modes: Offers an easy mechanism to toggle encryption for session data.
 * - Tagging: Allows classification of session data using contextual tags.
 */
final readonly class SessionBuilder implements SessionBuilderInterface
{
    /**
     * Constructor to initialize the session builder.
     *
     * @param SessionInterface     $session  Provides direct session data storage capabilities.
     * @param BagRegistryInterface $registry Manages session bags for modularity and extension.
     * @param SessionContext       $context  Holds contextual configuration for session operations.
     */
    public function __construct(
        private SessionInterface     $session,
        private BagRegistryInterface $registry,
        private SessionContext       $context
    ) {}

    /**
     * Magic method: Allows the `SessionBuilder` to be invoked like a function.
     *
     * Acts as shorthand for retrieving session data (`get()`) with an optional default.
     *
     * @param string     $key     The unique key to retrieve from session storage.
     * @param mixed|null $default The fallback value if the key does not exist (optional).
     *
     * @return mixed The value retrieved from the session or the default value if missing.
     */
    public function __invoke(string $key, mixed $default = null) : mixed
    {
        return $this->get(key: $key, default: $default);
    }

    /**
     * Retrieves data from the session with optional key resolution via namespace.
     *
     * @param string     $key     The unique key to retrieve from session storage.
     * @param mixed|null $default The default value if the key is not found (optional).
     *
     * @return mixed The value associated with the session key or the default value if not present.
     * @see resolveKey()
     *
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->session->get(
            key    : $this->resolveKey(key: $key),
            default: $default
        );
    }

    /**
     * Resolves the full session key by applying the active namespace if specified.
     *
     * @param string $key The base key provided by the consumer.
     *
     * @return string The fully resolved key, including namespace if applicable.
     */
    private function resolveKey(string $key) : string
    {
        return $this->context->namespace !== ''
            ? "{$this->context->namespace}.{$key}"
            : $key;
    }

    /**
     * Determines if a session key exists. Implements ArrayAccess for `isset()` use cases.
     *
     * @param mixed $offset The key to check.
     *
     * @return bool True if the session key exists; False otherwise.
     */
    public function offsetExists(mixed $offset) : bool
    {
        return $this->has(key: (string) $offset);
    }

    /**
     * Verifies the existence of a specific session entry.
     *
     * @param string $key The session key to check.
     *
     * @return bool True if the key exists; False otherwise.
     */
    public function has(string $key) : bool
    {
        return $this->session->has(key: $this->resolveKey(key: $key));
    }

    /**
     * Retrieves a session value via ArrayAccess, converting the offset to a string key.
     *
     * @param mixed $offset The key to retrieve.
     *
     * @return mixed The value associated with the key.
     */
    public function offsetGet(mixed $offset) : mixed
    {
        return $this->get(key: (string) $offset);
    }

    /**
     * Allows session values to be set via ArrayAccess. Resolves key via offset.
     *
     * @param mixed $offset The key where the value will be saved.
     * @param mixed $value  The value to be saved in the session.
     *
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value) : void
    {
        $this->set(key: (string) $offset, value: $value);
    }

    /**
     * Stores data in the session, respecting secure and TTL context configurations.
     *
     * @param string $key   The session key for storage.
     * @param mixed  $value The value to be stored.
     *
     * @return void
     * @see resolveKey()
     *
     */
    public function set(string $key, mixed $value) : void
    {
        $resolvedKey = $this->resolveKey(key: $key);

        // Secure storage
        if ($this->context->secure) {
            $this->session->put(key: $resolvedKey, value: $value);
            // TTL-based storage
        } elseif ($this->context->ttl !== null) {
            $this->session->putWithTTL(
                key  : $resolvedKey,
                value: $value,
                ttl  : $this->context->ttl
            );
            // Default storage
        } else {
            $this->session->put(key: $resolvedKey, value: $value);
        }
    }

    /**
     * Supports session data removal via ArrayAccess.
     *
     * @param mixed $offset The session key to unset.
     *
     * @return void
     */
    public function offsetUnset(mixed $offset) : void
    {
        $this->delete(key: (string) $offset);
    }

    /**
     * Deletes session data associated with a specific key after resolving via namespace.
     *
     * @param string $key The session key to remove.
     *
     * @return void
     */
    public function delete(string $key) : void
    {
        $this->session->delete(key: $this->resolveKey(key: $key));
    }

    /**
     * Updates the context to enable secure session mode.
     *
     * @return self A new `SessionBuilder` with secure mode enabled.
     */
    public function secure() : self
    {
        return $this->withContext($this->context->secure());
    }

    /**
     * Creates a new instance with an updated session context.
     *
     * @param SessionContext $context The updated context object.
     *
     * @return self A new `SessionBuilder` instance with updated configuration.
     */
    private function withContext(SessionContext $context) : self
    {
        return new self(
            session : $this->session,
            registry: $this->registry,
            context : $context
        );
    }

    /**
     * Sets a TTL (Time-To-Live) in seconds for session storage.
     *
     * @param int $seconds Number of seconds before session data expires.
     *
     * @return self A new `SessionBuilder` instance with TTL applied.
     */
    public function withTTL(int $seconds) : self
    {
        return $this->withContext($this->context->withTTL(ttl: $seconds));
    }

    /**
     * Tags the session context with a specific semantic grouping.
     *
     * @param SessionTag $tag The new tag to apply to the session.
     *
     * @return self A new `SessionBuilder` instance with the added tag.
     */
    public function tag(SessionTag $tag) : self
    {
        return $this->withContext($this->context->tag(tag: $tag));
    }

    /**
     * Adds a namespace for all subsequent session operations.
     *
     * @param string $namespace The namespace to apply.
     *
     * @return self A new `SessionBuilder` instance with the namespace applied.
     */
    public function withNamespace(string $namespace) : self
    {
        return $this->withContext($this->context->for(namespace: $namespace));
    }

    /**
     * Converts the current state of the `SessionBuilder` into a human-readable string.
     *
     * Useful for debugging, monitoring, or logging session context.
     *
     * @return string A string representation of the current session builder state.
     */
    #[Override]
    public function __toString() : string
    {
        return sprintf(
            'SessionBuilder(namespace="%s", seconds=%s, encrypt=%s, tags=%s)',
            $this->context->namespace,
            $this->context->ttl !== null ? $this->context->ttl . 's' : 'null',
            $this->context->secure ? 'true' : 'false',
            implode(
                ',',
                array_map(
                    static fn(SessionTag $tag) => $tag->name,
                    $this->context->tags
                )
            )
        );
    }
}