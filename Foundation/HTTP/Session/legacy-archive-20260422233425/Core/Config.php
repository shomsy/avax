<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core;

/**
 * Config - Configuration Value Object
 *
 * Immutable configuration for SessionManager initialization.
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
 *   $session = new SessionManager($store, $config);
 */
final readonly class Config
{
    public string|null $encryptionKey;
    public bool        $secure;
    public int|null    $ttl;

    /**
     * Config Constructor.
     *
     * @param int|null    $ttl           Default TTL in seconds.
     * @param bool        $secure        Enable auto-encryption by default.
     * @param string|null $encryptionKey Encryption key for secure values.
     */
    public function __construct(
        int|null    $ttl = null,
        bool|null   $secure = null,
        string|null $encryptionKey = null
    )
    {
        $secure              ??= false;
        $this->ttl           = $ttl;
        $this->secure        = $secure;
        $this->encryptionKey = $encryptionKey;
    }

    /**
     * Create default configuration.
     */
    public static function default() : self
    {
        return new self;
    }

    /**
     * Create secure configuration.
     *
     * @param string $encryptionKey The encryption key.
     */
    public static function secure(string $encryptionKey) : self
    {
        return new self(secure: true, encryptionKey: $encryptionKey);
    }

    /**
     * Create temporary configuration with TTL.
     *
     * @param int $ttl TTL in seconds.
     */
    public static function temporary(int $ttl) : self
    {
        return new self(ttl: $ttl);
    }
}
