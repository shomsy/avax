<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ApiVersioning\System\PublicSurface;

use Avax\Components\HTTP\ApiVersioning\System\Capabilities\Lifecycle\VersionRegistry;
use Avax\Components\HTTP\ApiVersioning\System\Capabilities\Resolution\VersionResolver;
use DateTimeInterface;
use Psr\Http\Message\RequestInterface;

final class ApiVersion
{
    private static VersionRegistry $versionRegistry;

    public static function resolve(RequestInterface $request) : ApiVersionResolved
    {
        return VersionResolver::resolve(request: $request, versionRegistry: self::registry());
    }

    private static function registry() : VersionRegistry
    {
        if (! isset(self::$versionRegistry)) {
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
}

final readonly class ApiVersionResolved
{
    public function __construct(
        public int                $version,
        public bool               $deprecated,
        public DateTimeInterface|null $sunset = null,
    ) {}
}
