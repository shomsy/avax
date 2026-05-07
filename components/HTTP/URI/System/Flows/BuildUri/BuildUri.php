<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\Flows\BuildUri;

final readonly class BuildUri
{
    /**
     * @param array{scheme?: string, host?: string, port?: int, path?: string, query?: string, fragment?: string} $parts
     */
    public function build(array $parts) : string
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
