<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ApiVersioning\System\PublicSurface;

use Avax\Components\HTTP\ApiVersioning\System\Capabilities\Lifecycle\VersionRegistry;
use Avax\Components\HTTP\ApiVersioning\System\Capabilities\Resolution\VersionResolver;
use DateTimeInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

/**
 * ApiVersion — PublicSurface entry point for HTTP API versioning.
 *
 * Receives and delegates. Does not assemble or instantiate runtime services.
 * The VersionRegistry must be configured during boot via setInstance().
 *
 * @see \Avax\Components\HTTP\ApiVersioning\System\Configuration\ApiVersioningServiceProvider
 */
final class ApiVersion
{
    private static ?VersionRegistry $versionRegistry = null;

    /**
     * Configure the version registry during boot.
     *
     * @throws RuntimeException if registry is already configured (prevents double-boot)
     */
    public static function setInstance(VersionRegistry $registry): void
    {
        if (self::$versionRegistry !== null) {
            throw new RuntimeException('ApiVersion registry is already configured.');
        }

        self::$versionRegistry = $registry;
    }

    public static function resolve(RequestInterface $request) : ApiVersionResolved
    {
        return VersionResolver::resolve(request: $request, versionRegistry: self::registry());
    }

    /**
     * @return VersionRegistry The configured version registry.
     * @throws RuntimeException if registry was not configured during boot.
     */
    private static function registry() : VersionRegistry
    {
        if (self::$versionRegistry === null) {
            throw new RuntimeException(
                'ApiVersion registry not configured. '
                . 'Ensure ApiVersioningServiceProvider is registered and booted.'
            );
        }

        return self::$versionRegistry;
    }

    public static function current() : int
    {
        return self::registry()->current();
    }

    public static function deprecated(int $version, DateTimeInterface $sunset) : void
    {
        self::registry()->markDeprecated($version, $sunset);
    }

    public static function supported() : array
    {
        return self::registry()->supported();
    }

    /**
     * Reset the static version registry. Required for test isolation and long-lived workers.
     */
    public static function reset(): void
    {
        self::$versionRegistry = null;
    }
}
