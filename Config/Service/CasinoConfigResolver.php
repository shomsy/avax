<?php

declare(strict_types=1);

namespace Infrastructure\Config\Service;

use Exception;
use Infrastructure\Config\CasinoConfig;
use InvalidArgumentException;

/**
 * Enum representing different casino configurations.
 * This encapsulates the configurations for various casinos,
 * providing methods to retrieve URLs and API keys.
 */
enum CasinoConfigResolver: string
{
    case EXTREME       = 'extreme';

    case BRANGO        = 'brango';

    case YABBY         = 'yabby';

    case LIMITLESS     = 'limitless';

    case PACIFIC_SPINS = 'pacific_spins';

    case BONUS_BLITZ   = 'bonus_blitz';

    case ORBIT_SPINS   = 'orbit_spins';

    /**
     * Retrieves the base URL for the specified casino ID.
     *
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function getUrl(string $casinoId) : string
    {
        return self::getConfig(casinoId: $casinoId)->getUrl();
    }

    /**
     * Retrieve configuration for the specified casino ID.
     *
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function getConfig(string $casinoId) : CasinoConfig
    {
        return new CasinoConfig($casinoId);
    }

    /**
     * Retrieves the API key for the specified casino ID.
     *
     *
     * @throws Exception
     */
    public static function getApiKey(string $casinoId) : string
    {
        return self::getConfig(casinoId: $casinoId)->getApiKey();
    }
}
