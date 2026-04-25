<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Caching;

use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;

final class BuildNotModifiedHeaders
{
    public function __invoke(
        ResponseInterface           $response,
        Etag|null                   $etag = null,
        LastModified|null           $lastModified = null,
        CacheControl|null           $cacheControl = null,
        #[SensitiveParameter] array $headers = []
    ) : ResponseInterface
    {
        $response = $response
            ->withoutHeader(name: 'Content-Type')
            ->withoutHeader(name: 'Content-Length');

        if ($etag !== null) {
            $response = $response->withHeader(name: 'ETag', value: $etag->toHeaderValue());
        }

        if ($lastModified !== null) {
            $response = $response->withHeader(name: 'Last-Modified', value: $lastModified->toHeaderValue());
        }

        if ($cacheControl !== null && $cacheControl->directives !== []) {
            $response = $response->withHeader(
                name : 'Cache-Control',
                value: new BuildCacheControlHeader()(cacheControl: $cacheControl),
            );
        }

        foreach ($headers as $name => $value) {
            $response = $response->withHeader(name: (string) $name, value: $value);
        }

        return $response;
    }
}
