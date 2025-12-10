<?php

declare(strict_types=1);

namespace Shomsy\Components\Foundation\HTTP\Session\Lifecycle;

/**
 * Session Component
 *
 * Config - Configuration Value Object
 *
 * Immutable configuration for SessionProvider initialization.
 *
 * Provides clean, readable configuration without constructor parameter soup.
 *
 * @example
 *   $config = new Config(
 *       ttl: 3600,
 *       secure: true,
 *       encryptionKey: 'secret'
 *   );
 *
 *   $session = new SessionProvider($store, $config);
 *
 * @author  Milos Stankovic
 * @package Shomsy\Components\Foundation\HTTP\Session\Lifecycle
 */
final readonly class Config
{
    /**
     * Config Constructor.
     *
     * @param int|null    $ttl           Default TTL in seconds.
     * @param bool        $secure        Enable auto-encryption by default.
     * @param string|null $encryptionKey Encryption key for secure values.
     */
    public function __construct(
        public int|null    $ttl = null,
        public bool        $secure = false,
        public string|null $encryptionKey = null
    ) {}

    /**
     * Create default configuration.
     *
     * @return self
     */
    public static function default() : self
    {
        return new self();
    }

    /**
     * Create secure configuration.
     *
     * @param string $encryptionKey The encryption key.
     *
     * @return self
     */
    public static function secure(string $encryptionKey) : self
    {
        return new self(secure: true, encryptionKey: $encryptionKey);
    }

    /**
     * Create temporary configuration with TTL.
     *
     * @param int $ttl TTL in seconds.
     *
     * @return self
     */
    public static function temporary(int $ttl) : self
    {
        return new self(ttl: $ttl);
    }
}
