<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Configuration\Builders;

use Avax\Components\HTTP\Request\System\Capabilities\Body\ParsedBody;
use Avax\Components\HTTP\Request\System\Capabilities\Body\RawBody;
use Avax\Components\HTTP\Request\System\Capabilities\Body\RequestBody;
use Avax\Components\HTTP\Request\System\Capabilities\Files\UploadedFiles;
use Avax\Components\HTTP\Request\System\Capabilities\Headers\RequestHeaders;
use Avax\Components\HTTP\Request\System\Capabilities\Uri\RequestUri;
use Avax\Components\HTTP\Request\System\PublicSurface\Request;

final class RequestBuilder
{
    private string $method = 'GET';

    private RequestUri $requestUri;

    private RequestHeaders $requestHeaders;

    private RequestBody $requestBody;

    private readonly UploadedFiles $uploadedFiles;

    /** @var array<string, mixed> */
    private array $serverParams = [];

    /** @var array<string, string> */
    private array $cookieParams = [];

    /** @var array<string, mixed> */
    private array $queryParams = [];

    /** @var array<string, mixed> */
    private array $attributes = [];

    private string $protocolVersion = '1.1';

    public function __construct()
    {
        $this->requestUri     = new RequestUri(path: '/');
        $this->requestHeaders = new RequestHeaders();
        $this->requestBody    = new RequestBody(rawBody: new RawBody(content: ''), parsedBody: new ParsedBody(data: null));
        $this->uploadedFiles  = new UploadedFiles();
    }

    public function withMethod(string $method) : self
    {
        $this->method = strtoupper(string: $method);

        return $this;
    }

    public function withUri(RequestUri $requestUri) : self
    {
        $this->requestUri = $requestUri;

        return $this;
    }

    /**
     * @param array<string, string|list<string>> $headers
     */
    public function withHeaders(array $headers) : self
    {
        $this->requestHeaders = new RequestHeaders(headers: $headers);

        return $this;
    }

    /**
     * @param array<string, mixed>|object|null $parsedBody
     */
    public function withBody(string $rawBody, array|object|null $parsedBody = null) : self
    {
        $this->requestBody = new RequestBody(
            rawBody   : new RawBody(content: $rawBody),
            parsedBody: new ParsedBody(data: $parsedBody),
        );

        return $this;
    }

    public function build() : Request
    {
        return new Request(
            method         : $this->method,
            requestUri     : $this->requestUri,
            requestHeaders : $this->requestHeaders,
            requestBody    : $this->requestBody,
            uploadedFiles  : $this->uploadedFiles,
            serverParams   : $this->serverParams,
            cookieParams   : $this->cookieParams,
            queryParams    : $this->queryParams,
            attributes     : $this->attributes,
            protocolVersion: $this->protocolVersion,
        );
    }
}
