<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\System\Capabilities\RequestData;

use Avax\Components\HTTP\Request\System\System\Capabilities\Body\RequestBody;
use Avax\Components\HTTP\Request\System\System\Capabilities\Files\UploadedFiles;
use Avax\Components\HTTP\Request\System\System\Capabilities\Headers\RequestHeaders;
use Avax\Components\HTTP\Request\System\System\Capabilities\Uri\RequestUri;

final readonly class RequestData
{
    public function __construct(
        public string         $method,
        public RequestUri     $uri,
        public RequestHeaders $headers,
        public RequestBody    $body,
        public UploadedFiles  $files,
        public array          $serverParams,
        public array          $cookieParams,
        public array          $queryParams,
        public array          $attributes,
        public string         $protocolVersion,
    ) {}

    public function withMethod(string $method) : self
    {
        return new self(
            method         : $method,
            uri            : $this->uri,
            headers        : $this->headers,
            body           : $this->body,
            files          : $this->files,
            serverParams   : $this->serverParams,
            cookieParams   : $this->cookieParams,
            queryParams    : $this->queryParams,
            attributes     : $this->attributes,
            protocolVersion: $this->protocolVersion,
        );
    }

    public function withProtocolVersion(string $version) : self
    {
        return new self(
            method         : $this->method,
            uri            : $this->uri,
            headers        : $this->headers,
            body           : $this->body,
            files          : $this->files,
            serverParams   : $this->serverParams,
            cookieParams   : $this->cookieParams,
            queryParams    : $this->queryParams,
            attributes     : $this->attributes,
            protocolVersion: $version,
        );
    }

    public function withUri(RequestUri $requestUri) : self
    {
        return new self(
            method         : $this->method,
            uri            : $requestUri,
            headers        : $this->headers,
            body           : $this->body,
            files          : $this->files,
            serverParams   : $this->serverParams,
            cookieParams   : $this->cookieParams,
            queryParams    : $this->queryParams,
            attributes     : $this->attributes,
            protocolVersion: $this->protocolVersion,
        );
    }

    public function withCookieParams(array $cookies) : self
    {
        return new self(
            method         : $this->method,
            uri            : $this->uri,
            headers        : $this->headers,
            body           : $this->body,
            files          : $this->files,
            serverParams   : $this->serverParams,
            cookieParams   : $cookies,
            queryParams    : $this->queryParams,
            attributes     : $this->attributes,
            protocolVersion: $this->protocolVersion,
        );
    }

    public function withQueryParams(array $query) : self
    {
        return new self(
            method         : $this->method,
            uri            : $this->uri,
            headers        : $this->headers,
            body           : $this->body,
            files          : $this->files,
            serverParams   : $this->serverParams,
            cookieParams   : $this->cookieParams,
            queryParams    : $query,
            attributes     : $this->attributes,
            protocolVersion: $this->protocolVersion,
        );
    }

    public function withAttribute(string $name, mixed $value) : self
    {
        $attributes        = $this->attributes;
        $attributes[$name] = $value;

        return new self(
            method         : $this->method,
            uri            : $this->uri,
            headers        : $this->headers,
            body           : $this->body,
            files          : $this->files,
            serverParams   : $this->serverParams,
            cookieParams   : $this->cookieParams,
            queryParams    : $this->queryParams,
            attributes     : $attributes,
            protocolVersion: $this->protocolVersion,
        );
    }

    public function withoutAttribute(string $name) : self
    {
        $attributes = $this->attributes;
        unset($attributes[$name]);

        return new self(
            method         : $this->method,
            uri            : $this->uri,
            headers        : $this->headers,
            body           : $this->body,
            files          : $this->files,
            serverParams   : $this->serverParams,
            cookieParams   : $this->cookieParams,
            queryParams    : $this->queryParams,
            attributes     : $attributes,
            protocolVersion: $this->protocolVersion,
        );
    }
}
