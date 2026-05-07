<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\PublicSurface;

final readonly class Uri
{
    /**
     * @return array{scheme: string|null, host: string|null, port: int|null, path: string, query: string|null,
     *                       fragment: string|null}
     */
    public static function parse(string $uri) : array
    {
        $parts = parse_url($uri);

        return [
            'scheme'   => $parts['scheme'] ?? null,
            'host'     => $parts['host'] ?? null,
            'port'     => $parts['port'] ?? null,
            'path'     => $parts['path'] ?? '',
            'query'    => $parts['query'] ?? null,
            'fragment' => $parts['fragment'] ?? null,
        ];
    }

    /**
     * @param array{scheme?: string, host?: string, port?: int, path?: string, query?: string, fragment?: string} $parts
     */
    public static function build(array $parts) : string
    {
        $uri = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? 'localhost');

        if (isset($parts['port'])) {
            $uri .= ':' . $parts['port'];
        }

        $uri .= $parts['path'] ?? '/';

        if (isset($parts['query'])) {
            $uri .= '?' . $parts['query'];
        }

        if (isset($parts['fragment'])) {
            $uri .= '#' . $parts['fragment'];
        }

        return $uri;
    }
}
