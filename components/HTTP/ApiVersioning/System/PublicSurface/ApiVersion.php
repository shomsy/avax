<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ApiVersioning\System\PublicSurface;

use Avax\Components\HTTP\ApiVersioning\System\Capabilities\Lifecycle\VersionRegistry;
use Avax\Components\HTTP\ApiVersioning\System\Capabilities\Resolution\VersionResolver;
use DateTimeInterface;
use Psr\Http\Message\RequestInterface;

final class ApiVersion
{
    private static ?VersionRegistry $versionRegistry = null;

    public static function resolve(RequestInterface $request) : ApiVersionResolved
    {
        return VersionResolver::resolve(request: $request, versionRegistry: self::registry());
    }

    private static function registry() : VersionRegistry
    {
        if (self::$versionRegistry === null) {
            self::$versionRegistry = new VersionRegistry();
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
     * Reset the static version registry. Required for test isolation.
     */
    public static function reset(): void
    {
        self::$versionRegistry = null;
    }

    /**
     * Replace the version registry (for testing or DI injection).
     */
    public static function setInstance(VersionRegistry $registry): void
    {
        self::$versionRegistry = $registry;
    }
}

final readonly class ApiVersionResolved
{
    public function __construct(
        public int                $version,
        public bool               $deprecated,
        public DateTimeInterface|null $sunset = null,
    ) {}
}
