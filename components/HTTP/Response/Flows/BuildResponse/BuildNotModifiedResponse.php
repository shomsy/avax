<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Flows\BuildResponse;

use Avax\Components\HTTP\Response\Capabilities\Caching\BuildNotModifiedHeaders;
use Avax\Components\HTTP\Response\Capabilities\Caching\CacheControl;
use Avax\Components\HTTP\Response\Capabilities\Caching\Etag;
use Avax\Components\HTTP\Response\Capabilities\Caching\LastModified;
use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;

final class BuildNotModifiedResponse
{
    public function __invoke(
        Etag|null                   $etag = null,
        LastModified|null           $lastModified = null,
        CacheControl|null           $cacheControl = null,
        #[SensitiveParameter] array $headers = [],
    ) : ResponseInterface
    {
        $response = new BuildEmptyResponse()(status: 304);

        return new BuildNotModifiedHeaders()(
            response    : $response,
            etag        : $etag,
            lastModified: $lastModified,
            cacheControl: $cacheControl,
            headers     : $headers,
        );
    }
}
