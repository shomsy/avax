<?php

declare(strict_types=1);

namespace Gemini\Config;

/**
 * Class AuthConfig
 *
 * This class handles configuration settings for authentication,
 * allowing for dynamic retrieval from environment variables with
 * fallback to default values. This approach supports flexible and
 * easily configurable authentication mechanisms.
 */
class AuthConfig
{
    /** @var string DEFAULT_GUARD Default authentication guard */
    public const string DEFAULT_GUARD = 'session';

    /** @var string DEFAULT_PROVIDER Default authentication provider */
    public const string DEFAULT_PROVIDER = 'userProvider';

    /**
     * Retrieves the current authentication guard.
     *
     * @return string
     * The guard is first fetched from the environment variable 'AUTH_GUARD'.
     * If not set, it falls back to the default guard defined by DEFAULT_GUARD.
     */
    public static function getGuard() : string
    {
        // Fetch guard from environment or use default
        return env(key: 'AUTH_GUARD') ?: self::DEFAULT_GUARD;
    }

    /**
     * Retrieves the current authentication provider.
     *
     * @return string
     * The provider is first fetched from the environment variable 'AUTH_PROVIDER'.
     * If not set, it falls back to the default provider defined by DEFAULT_PROVIDER.
     */
    public static function getProvider() : string
    {
        // Fetch provider from environment or use default
        return env(key: 'AUTH_PROVIDER') ?: self::DEFAULT_PROVIDER;
    }
}
