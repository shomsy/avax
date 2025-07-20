<?php

declare(strict_types=1);

namespace Infrastructure\Config;

use InvalidArgumentException;

/**
 * CasinoConfig is an immutable class that holds configuration data for casino connections.
 * It uses a factory pattern to manage caching instances and validates casino IDs upon creation.
 */
final class CasinoConfig
{
    /**
     * CONFIG_KEYS holds configuration for various services.
     * Each service has its own URL and API key.
     *
     * This structure allows for easy extension and maintenance
     * by simply adding new services or modifying existing ones.
     *
     * Keys:
     * - 'extreme', 'brango', 'yabby', 'limitless', 'pacific_spins',
     *   'bonus_blitz', 'orbit_spins':
     *     - 'url'   : The endpoint URL for the service.
     *     - 'apiKey': The API key for accessing the service.
     *
     * This setup ensures that individual service configurations are
     * encapsulated and can be managed independently.
     */
    private const array CONFIG_KEYS = [
        'extreme'       => [
            'url'    => 'EXTREME_URL',
            'apiKey' => 'EXTREME_API_KEY',
        ],
        'brango'        => [
            'url'    => 'BRANGO_URL',
            'apiKey' => 'BRANGO_API_KEY',
        ],
        'yabby'         => [
            'url'    => 'YABBY_URL',
            'apiKey' => 'YABBY_API_KEY',
        ],
        'limitless'     => [
            'url'    => 'LIMITLESS_URL',
            'apiKey' => 'LIMITLESS_API_KEY',
        ],
        'pacific_spins' => [
            'url'    => 'PACIFIC_SPINS_URL',
            'apiKey' => 'PACIFIC_SPINS_API_KEY',
        ],
        'bonus_blitz'   => [
            'url'    => 'BONUS_BLITZ_URL',
            'apiKey' => 'BONUS_BLITZ_API_KEY',
        ],
        'orbit_spins'   => [
            'url'    => 'ORBIT_SPINS_URL',
            'apiKey' => 'ORBIT_SPINS_API_KEY',
        ],
    ];

    /**
     *
     * Manages a simple cache mechanism.
     *
     * This array handles the caching of data to improve performance
     * and reduce duplicated computation. It provides methods for
     * adding, retrieving, and clearing cached entries.
     */
    private static array $cache = [];

    /**
     * Private constructor to initialize configuration with URL and API key.
     *
     * @param string $casinoId The identifier of the casino.
     * @param string $url      The URL for the casino API.
     * @param string $apiKey   The API key for the casino API.
     */
    public function __construct(string $casinoId, private readonly string $url, private readonly string $apiKey)
    {
        self::ensureValidCasinoId(casinoId: $casinoId);
    }

    /**
     * Validates if the provided casinoId exists in CONFIG_KEYS.
     *
     * @param string $casinoId The casino ID to validate.
     *
     * @throws InvalidArgumentException if the casino ID is not valid.
     */
    private static function ensureValidCasinoId(string $casinoId) : void
    {
        if (! isset(self::CONFIG_KEYS[$casinoId])) {
            throw new InvalidArgumentException(message: sprintf("Invalid casino ID: %s", $casinoId));
        }
    }

    /**
     * Factory method to get or create and cache an instance of CasinoConfig.
     *
     * @param string $casinoId The casino identifier.
     *
     * @return self Cached or newly created instance.
     *
     * @throws InvalidArgumentException if the casino ID or environment variables are invalid.
     */
    public static function getInstance(string $casinoId) : self
    {
        // Return the cached instance if it exists
        if (isset(self::$cache[$casinoId])) {
            return self::$cache[$casinoId];
        }

        self::ensureValidCasinoId(casinoId: $casinoId); // Validate the casino ID
        $url    = self::getEnvVariable(envKey: self::CONFIG_KEYS[$casinoId]['url']);
        $apiKey = self::getEnvVariable(envKey: self::CONFIG_KEYS[$casinoId]['apiKey']);

        // Cache the new instance and return it
        return self::$cache[$casinoId] = new self(casinoId: $casinoId, url: $url, apiKey: $apiKey);
    }

    /**
     * Fetches the environment variable value by key.
     *
     * @param string $envKey The key for the environment variable.
     *
     * @return string The value of the environment variable.
     *
     * @throws InvalidArgumentException if the environment variable is not set.
     */
    private static function getEnvVariable(string $envKey) : string
    {
        $value = getenv($envKey);
        if ($value === false) {
            throw new InvalidArgumentException(message: sprintf("Environment variable %s is not set.", $envKey));
        }

        return $value;
    }

    /**
     * Retrieves the URL for the casino.
     *
     * @return string The configured URL.
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * Retrieves the API key for the casino.
     *
     * @return string The configured API key.
     */
    public function getApiKey() : string
    {
        return $this->apiKey;
    }
}
