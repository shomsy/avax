<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Flows\CreateRequestFromRuntime;

use Avax\Components\HTTP\Request\System\Capabilities\Body\ParsedBody;
use Avax\Components\HTTP\Request\System\Capabilities\Body\RawBody;
use Avax\Components\HTTP\Request\System\Capabilities\Body\RequestBody;
use Avax\Components\HTTP\Request\System\Capabilities\Files\UploadedFiles;
use Avax\Components\HTTP\Request\System\Capabilities\Headers\RequestHeaders;
use Avax\Components\HTTP\Request\System\Capabilities\Uri\RequestUri;
use Avax\Components\HTTP\Request\System\PublicSurface\Request;

final class CreateRequestFromRuntime
{
    /**
     * @param array<string, string|list<string>> $headers
     * @param array<string, mixed>               $serverParams
     * @param array<string, string>              $cookieParams
     * @param array<string, mixed>               $queryParams
     * @param array<string, mixed>               $attributes
     */
    public function execute(
        string            $method = 'GET',
        RequestUri|string $uri = '/',
        array             $headers = [],
        string            $rawBody = '',
        array|object|null $parsedBody = null, UploadedFiles|null $uploadedFiles = null,
        array             $serverParams = [],
        array             $cookieParams = [],
        array             $queryParams = [],
        array             $attributes = [],
        string            $protocolVersion = '1.1',
    ) : Request
    {
        return new Request(
            method         : $method,
            requestUri     : $uri instanceof RequestUri ? $uri : $this->uriFromString(uri: $uri),
            requestHeaders : new RequestHeaders(headers: $headers),
            requestBody    : new RequestBody(
                                 rawBody   : new RawBody(content: $rawBody),
                                 parsedBody: new ParsedBody(data: $parsedBody),
                             ),
            uploadedFiles  : $uploadedFiles ?? new UploadedFiles(),
            serverParams   : $serverParams,
            cookieParams   : $cookieParams,
            queryParams    : $queryParams,
            attributes     : $attributes,
            protocolVersion: $protocolVersion,
        );
    }

    private function uriFromString(string $uri) : RequestUri
    {
        $parts = parse_url(url: $uri) ?: [];

        return new RequestUri(
            scheme: (string) ($parts['scheme'] ?? ''),
            host  : (string) ($parts['host'] ?? ''),
            path  : (string) ($parts['path'] ?? '/'),
            query : (string) ($parts['query'] ?? ''),
        );
    }
}
