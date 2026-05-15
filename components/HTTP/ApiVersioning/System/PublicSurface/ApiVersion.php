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

    /**
     * Resolve the API version for an incoming HTTP request.
     *
     * Delegates to VersionResolver using the configured VersionRegistry.
     *
     * @param RequestInterface $request The incoming HTTP request to inspect for version information.
     * @return ApiVersionResolved The resolved version, deprecation status, and optional sunset date.
     * @throws RuntimeException if the VersionRegistry was not configured during boot.
     */
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

    /**
     * Get the current API version.
     *
     * @return int The current API version number.
     * @throws RuntimeException if the VersionRegistry was not configured during boot.
     */
    public static function current() : int
    {
        return self::registry()->current();
    }

    /**
     * Mark an API version as deprecated with a sunset date.
     *
     * @param int $version The API version number to deprecate.
     * @param DateTimeInterface $sunset The date after which this version will no longer be supported.
     * @throws RuntimeException if the VersionRegistry was not configured during boot.
     */
    public static function deprecated(int $version, DateTimeInterface $sunset) : void
    {
        self::registry()->markDeprecated($version, $sunset);
    }

    /**
     * Get the list of supported API versions.
     *
     * @return list<int> Sorted list of supported API version numbers.
     * @throws RuntimeException if the VersionRegistry was not configured during boot.
     */
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
