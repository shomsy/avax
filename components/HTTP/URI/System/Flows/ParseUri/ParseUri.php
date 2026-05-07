<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\Flows\ParseUri;

final readonly class ParseUri
{
    /**
     * @return array{scheme: string|null, host: string|null, port: int|null, path: string, query: string|null,
     *                       fragment: string|null}
     */
    public function parse(string $uri) : array
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
}
