<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core;

use Avax\HTTP\Session\Enums\SessionTag;

/**
 * SessionContext Value Object
 *
 * Immutable configuration context for session operations.
 *
 * This value object encapsulates all contextual configuration for session
 * operations including namespace isolation, TTL expiration, encryption,
 * and semantic tagging.
 *
 * Enterprise Rules:
 * - Immutability: All modifications return new instances.
 * - Validation: Guards against invalid states.
 * - Type Safety: Strict typing throughout.
 *
 * Usage:
 *   $context = SessionContext::for('cart')
 *       ->secure()
 *       ->withTTL(3600);
 *
 * @package Avax\HTTP\Session\Core
 */
final readonly class SessionContext
{
    /**
     * SessionContext Constructor.
     *
     * @param string           $namespace Logical scope identifier (e.g. "user", "cart", "flash").
     * @param bool             $secure    Whether encryption is enforced for session values.
     * @param int|null         $ttl       Optional time-to-live in seconds for temporary data.
     * @param list<SessionTag> $tags      Domain-relevant tags for organizational metadata.
     */
    public function __construct(
        public string   $namespace,
        public bool     $secure = false,
        public int|null $ttl = null,
        public array    $tags = []
    ) {
        // Guard: Validate TTL is positive if set.
        if ($this->ttl !== null && $this->ttl <= 0) {
            throw new \InvalidArgumentException(
                message: "TTL must be positive, got: {$this->ttl}"
            );
        }

        // Guard: Validate namespace doesn't contain special characters.
        if (preg_match('/[^a-zA-Z0-9_\-.]/', $this->namespace)) {
            throw new \InvalidArgumentException(
                message: "Namespace contains invalid characters: {$this->namespace}"
            );
        }
    }

    /**
     * Factory: Create context for a specific namespace.
     *
     * @param string $namespace Logical namespace for grouping session data.
     *
     * @return self
     */
    public static function for(string $namespace): self
    {
        // Trim dots from namespace edges.
        $namespace = trim($namespace, '.');

        return new self(namespace: $namespace);
    }

    /**
     * Factory: Create default context.
     *
     * @return self
     */
    public static function default(): self
    {
        return new self(namespace: 'default');
    }

    /**
     * Clone with encryption enabled.
     *
     * Returns a new instance with secure flag set to true.
     *
     * @return self New immutable instance.
     */
    public function secure(): self
    {
        return new self(
            namespace: $this->namespace,
            secure: true,
            ttl: $this->ttl,
            tags: $this->tags
        );
    }

    /**
     * Clone with TTL set.
     *
     * Returns a new instance with specified TTL.
     *
     * @param int $ttl Time-to-live in seconds.
     *
     * @return self New immutable instance.
     */
    public function withTTL(int $ttl): self
    {
        return new self(
            namespace: $this->namespace,
            secure: $this->secure,
            ttl: $ttl,
            tags: $this->tags
        );
    }

    /**
     * Clone with tag added.
     *
     * Returns a new instance with the tag appended.
     *
     * @param SessionTag $tag The tag to add.
     *
     * @return self New immutable instance.
     */
    public function tag(SessionTag $tag): self
    {
        return new self(
            namespace: $this->namespace,
            secure: $this->secure,
            ttl: $this->ttl,
            tags: array_unique([...$this->tags, $tag])
        );
    }

    /**
     * Clone with different namespace.
     *
     * Returns a new instance with updated namespace.
     *
     * @param string $namespace The new namespace.
     *
     * @return self New immutable instance.
     */
    public function forNamespace(string $namespace): self
    {
        // Trim dots from namespace edges.
        $namespace = trim($namespace, '.');

        return new self(
            namespace: $namespace,
            secure: $this->secure,
            ttl: $this->ttl,
            tags: $this->tags
        );
    }

    /**
     * String representation for debugging.
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            'SessionContext(namespace="%s", secure=%s, ttl=%s, tags=%d)',
            $this->namespace,
            $this->secure ? 'true' : 'false',
            $this->ttl ?? 'null',
            count($this->tags)
        );
    }
}
