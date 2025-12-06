<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core;

/**
 * SessionContextFactory
 *
 * Factory for creating SessionContext instances.
 *
 * This factory simplifies context creation and provides
 * common presets for testing and development.
 *
 * @package Avax\HTTP\Session\Core
 */
final class SessionContextFactory
{
    /**
     * Create default context.
     *
     * @return SessionContext
     */
    public static function default(): SessionContext
    {
        return new SessionContext(
            namespace: 'default',
            secure: false,
            ttl: null,
            tags: [],
            custom: []
        );
    }

    /**
     * Create context for specific namespace.
     *
     * @param string $namespace The namespace.
     *
     * @return SessionContext
     */
    public static function forNamespace(string $namespace): SessionContext
    {
        return new SessionContext(
            namespace: $namespace,
            secure: false,
            ttl: null,
            tags: [],
            custom: []
        );
    }

    /**
     * Create secure context.
     *
     * @return SessionContext
     */
    public static function secure(): SessionContext
    {
        return new SessionContext(
            namespace: 'default',
            secure: true,
            ttl: null,
            tags: [],
            custom: []
        );
    }

    /**
     * Create temporary context with TTL.
     *
     * @param int $ttl Time-to-live in seconds.
     *
     * @return SessionContext
     */
    public static function temporary(int $ttl): SessionContext
    {
        return new SessionContext(
            namespace: 'default',
            secure: false,
            ttl: $ttl,
            tags: [],
            custom: []
        );
    }

    /**
     * Create context from array.
     *
     * @param array<string, mixed> $data The context data.
     *
     * @return SessionContext
     */
    public static function fromArray(array $data): SessionContext
    {
        return new SessionContext(
            namespace: $data['namespace'] ?? 'default',
            secure: $data['secure'] ?? false,
            ttl: $data['ttl'] ?? null,
            tags: $data['tags'] ?? [],
            custom: $data['custom'] ?? []
        );
    }
}
