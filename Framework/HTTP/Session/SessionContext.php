<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session;

use Gemini\HTTP\Session\Enums\SessionTag;

use function array_unique;

/**
 * Immutable value object for contextual session configuration.
 *
 * Encapsulates namespace, TTL, encryption, and tags.
 */
final readonly class SessionContext
{
    /**
     * @param string           $namespace Logical scope identifier (e.g. "user", "cart", "flash").
     * @param bool             $secure    Whether encryption is enforced for the session values.
     * @param int|null         $ttl       Optional time-to-live in seconds for temporary data.
     * @param list<SessionTag> $tags      Domain-relevant tags for organizational metadata.
     */
    public function __construct(
        public string   $namespace,
        public bool     $secure = false,
        public int|null $ttl = null,
        public array    $tags = []
    ) {}

    /**
     * Factory-style constructor for namespaced context.
     *
     * @param string $namespace Logical namespace for grouping session data.
     *
     * @return self
     */
    public static function for(string $namespace) : self
    {
        return new self(namespace: trim($namespace, '.'));
    }

    /**
     * Clones the context with encryption enabled.
     */
    public function secure() : self
    {
        return new self(
            namespace: $this->namespace,
            secure   : true,
            ttl      : $this->ttl,
            tags     : $this->tags
        );
    }

    /**
     * Clones the context with a new TTL.
     *
     * @param int $ttl Time-to-live in seconds.
     */
    public function withTTL(int $ttl) : self
    {
        return new self(
            namespace: $this->namespace,
            secure   : $this->secure,
            ttl      : $ttl,
            tags     : $this->tags
        );
    }

    /**
     * Clones the context with a new tag appended.
     *
     * @param SessionTag $tag
     *
     * @return \Gemini\HTTP\Session\SessionContext
     */
    public function tag(SessionTag $tag) : self
    {
        return new self(
            namespace: $this->namespace,
            secure   : $this->secure,
            ttl      : $this->ttl,
            tags     : array_unique([...$this->tags, $tag])
        );
    }

    /**
     * Clones the context with a different namespace.
     *
     * @param string $namespace
     *
     * @return \Gemini\HTTP\Session\SessionContext
     */
    public function forNamespace(string $namespace) : self
    {
        return new self(
            namespace: trim($namespace, '.'),
            secure   : $this->secure,
            ttl      : $this->ttl,
            tags     : $this->tags
        );
    }
}
