<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Uri;

use InvalidArgumentException;

final readonly class ParseUriString
{
    public static function parse(string $uri): Uri
    {
        $parts = parse_url($uri);
        if ($parts === false) {
            throw new InvalidArgumentException("Invalid URI: {$uri}");
        }

        return new Uri(
            scheme: $parts['scheme'] ?? '',
            host: $parts['host'] ?? '',
            path: $parts['path'] ?? '/',
            port: $parts['port'] ?? null,
            query: $parts['query'] ?? '',
            fragment: $parts['fragment'] ?? '',
            user: $parts['user'] ?? '',
            password: $parts['pass'] ?? null
        );
    }
}
