<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ApiVersioning\System\Capabilities\Resolution;

use Avax\Components\HTTP\ApiVersioning\System\Capabilities\Lifecycle\VersionRegistry;
use Avax\Components\HTTP\ApiVersioning\System\PublicSurface\ApiVersionResolved;
use Psr\Http\Message\RequestInterface;

final readonly class VersionResolver
{
    public static function resolve(RequestInterface $request, ?VersionRegistry $versionRegistry = null): ApiVersionResolved
    {
        $versionRegistry ??= new VersionRegistry();
        $version = self::readVersion(request: $request) ?? $versionRegistry->current();

        return new ApiVersionResolved(
            version   : $version,
            deprecated: $versionRegistry->deprecated(version: $version),
            sunset    : $versionRegistry->sunset(version: $version),
        );
    }

    private static function readVersion(RequestInterface $request): ?int
    {
        $headerVersion = self::positiveInt(value: $request->getHeaderLine('X-API-Version'));

        if ($headerVersion !== null) {
            return $headerVersion;
        }

        $apiVersionHeader = self::positiveInt(value: $request->getHeaderLine('API-Version'));

        if ($apiVersionHeader !== null) {
            return $apiVersionHeader;
        }

        $acceptVersion = self::readAcceptVersion(accept: $request->getHeaderLine('Accept'));

        if ($acceptVersion !== null) {
            return $acceptVersion;
        }

        parse_str(string: $request->getUri()->getQuery(), result: $query);
        $queryVersion = $query['version'] ?? $query['api_version'] ?? null;

        return self::positiveInt(value: $queryVersion);
    }

    private static function positiveInt(mixed $value): ?int
    {
        if (is_int(value: $value)) {
            return $value > 0 ? $value : null;
        }

        if (! is_string(value: $value) || ! ctype_digit(text: $value)) {
            return null;
        }

        $version = (int) $value;

        return $version > 0 ? $version : null;
    }

    private static function readAcceptVersion(string $accept): ?int
    {
        if (preg_match(pattern: '/(?:v|version=)(\d+)/i', subject: $accept, matches: $matches) !== 1) {
            return null;
        }

        return self::positiveInt(value: $matches[1]);
    }
}
