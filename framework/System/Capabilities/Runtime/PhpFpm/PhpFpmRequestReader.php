<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\PhpFpm;

use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;

final readonly class PhpFpmRequestReader
{
    /**
     * @param array<string, string> $server
     * @param array<string, mixed> $query
     * @param array<string, mixed> $parsedBody
     */
    public function read(
        array $server,
        array $query = [],
        array $parsedBody = [],
        string $body = null,
    ) : RuntimeRequest
    {
        $method     = $server['REQUEST_METHOD'] ?? 'GET';
        $uri        = $server['REQUEST_URI']    ?? '/';
        $attributes = ['query' => $query, 'parsedBody' => $parsedBody];
        $headers    = [];

        foreach ($server as $key => $value) {
            if (! str_starts_with(haystack: $key, needle: 'HTTP_')) {
                continue;
            }

            $headerName             = str_replace('_', '-', substr($key, 5));
            $headers[$headerName][] = $value;
        }

        return new RuntimeRequest(
            method    : $method,
            uri       : $uri,
            headers   : $headers,
            body      : $body,
            attributes: $attributes,
        );
    }
}
