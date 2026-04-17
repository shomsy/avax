<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestAttributes\RequestAttributes;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\ParsedBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestCookies\RequestCookies;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders\RequestHeaders;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestSession\RequestSession;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\UploadedFiles;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\URI\UriBuilder;
use Psr\Http\Message\UriInterface;
use SensitiveParameter;

/**
 * RequestInit - State container for ServerRequest.
 *
 * Holds all request state and initialization logic.
 * ServerRequest is just a thin fluent API wrapper around this.
 */
final readonly class RequestInit
{
    public UriInterface $uri;

    public array $serverParams;

    public array $queryParams;

    public string|null $requestTarget;

    public string $method;

    public string $protocolVersion;

    public array $headers;

    public RequestSession|null $session;

    public RequestBody $body;

    public RequestHeaders $requestHeaders;

    public RequestCookies $cookies;

    public UploadedFiles $uploadedFiles;

    public ParsedBody $parsedBody;

    public RequestAttributes $attributes;

    public function __construct(
        RequestBody                           $body,
        string                                $method,
        UriInterface                          $uri,
        #[SensitiveParameter] RequestHeaders $requestHeaders,
        array                                 $serverParams,
        string                                $requestTarget,
        RequestCookies                        $cookies,
        array                                 $queryParams,
        UploadedFiles                         $uploadedFiles,
        ParsedBody                            $parsedBody,
        RequestAttributes                     $attributes,
        #[SensitiveParameter]
        RequestSession|null $session,
        string                                $protocolVersion,
    ) {
        $this->body = $body;
        $this->method = $method;
        $this->uri = $uri;
        $this->requestHeaders = $requestHeaders;
        $this->serverParams = $serverParams;
        $this->requestTarget = $requestTarget !== ''
            ? $requestTarget
            : $this->computeRequestTarget(uri: $uri);
        $this->cookies = $cookies;
        $this->queryParams = $queryParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->parsedBody = $parsedBody;
        $this->attributes = $attributes;
        $this->session = $session;
        $this->protocolVersion = $protocolVersion;
        $this->headers = $requestHeaders->all();
    }

    private function computeRequestTarget(UriInterface $uri): string
    {
        $path = $uri->getPath() ?: '/';
        $query = $uri->getQuery();

        return $query !== '' ? $path.'?'.$query : $path;
    }

    public static function defaults(): self
    {
        $uri = UriBuilder::createFromString(uri: 'http://localhost');
        $requestHeaders = new RequestHeaders;

        return new self(
            body: new RequestBody(stream: new Stream(stream: fopen('php://temp', 'r+'))),
            method: 'GET',
            uri: $uri,
            requestHeaders: $requestHeaders,
            serverParams: [],
            requestTarget: '',
            cookies: new RequestCookies,
            queryParams: [],
            uploadedFiles: new UploadedFiles,
            parsedBody: new ParsedBody,
            attributes: new RequestAttributes,
            session: null,
            protocolVersion: '1.1',
        );
    }

    public static function fromGlobals(
        array|null $server = null,
        array|null $query = null,
        array|null $cookie = null,
        array|null $files = null,
    ): self {
        $server ??= [];
        $query ??= [];
        $cookie ??= [];
        $files ??= [];

        $method = strtoupper($server['REQUEST_METHOD'] ?? 'GET');
        $protocolVersion = self::normalizeProtocolVersion(version: $server['SERVER_PROTOCOL'] ?? '1.1');

        $uri = self::createUriFromServer(server: $server);
        $requestTarget = $uri->getPath().($uri->getQuery() ? '?'.$uri->getQuery() : '');

        $headers = self::extractHeaders(server: $server);
        $requestHeaders = new RequestHeaders(headers: $headers);

        return new self(
            body: new RequestBody(stream: new Stream(stream: fopen('php://temp', 'r+'))),
            method: $method,
            uri: $uri,
            requestHeaders: $requestHeaders,
            serverParams: $server,
            requestTarget: $requestTarget,
            cookies: new RequestCookies(cookies: $cookie),
            queryParams: $query,
            uploadedFiles: new UploadedFiles(files: $files),
            parsedBody: new ParsedBody,
            attributes: new RequestAttributes,
            session: null,
            protocolVersion: $protocolVersion,
        );
    }

    public static function fromSlices(
        array|null        $queryParams = null,
        array|object|null $parsedBody = null,
        string            $method = 'GET',
    ): self {
        $queryParams ??= [];
        $parsedBodyData = is_array($parsedBody) ? $parsedBody : (is_object($parsedBody) ? (array) $parsedBody : []);

        return self::defaults()
            ->withMethod(method: $method)
            ->withQueryParams(queryParams: $queryParams)
            ->withParsedBody(parsedBody: new ParsedBody(data: $parsedBodyData));
    }

    private function with(string $prop, mixed $value): self
    {
        return match ($prop) {
            'method' => new self(
                body: $this->body, method: $value, uri: $this->uri,
                requestHeaders: $this->requestHeaders, serverParams: $this->serverParams,
                requestTarget: $this->requestTarget, cookies: $this->cookies,
                queryParams: $this->queryParams, uploadedFiles: $this->uploadedFiles,
                parsedBody: $this->parsedBody, attributes: $this->attributes,
                session: $this->session, protocolVersion: $this->protocolVersion,
            ),
            'uri' => new self(
                body: $this->body, method: $this->method, uri: $value,
                requestHeaders: $this->requestHeaders, serverParams: $this->serverParams,
                requestTarget: '', cookies: $this->cookies,
                queryParams: $this->queryParams, uploadedFiles: $this->uploadedFiles,
                parsedBody: $this->parsedBody, attributes: $this->attributes,
                session: $this->session, protocolVersion: $this->protocolVersion,
            ),
            'queryParams' => new self(
                body: $this->body, method: $this->method, uri: $this->uri,
                requestHeaders: $this->requestHeaders, serverParams: $this->serverParams,
                requestTarget: $this->requestTarget, cookies: $this->cookies,
                queryParams: $value, uploadedFiles: $this->uploadedFiles,
                parsedBody: $this->parsedBody, attributes: $this->attributes,
                session: $this->session, protocolVersion: $this->protocolVersion,
            ),
            'parsedBody' => new self(
                body: $this->body, method: $this->method, uri: $this->uri,
                requestHeaders: $this->requestHeaders, serverParams: $this->serverParams,
                requestTarget: $this->requestTarget, cookies: $this->cookies,
                queryParams: $this->queryParams, uploadedFiles: $this->uploadedFiles,
                parsedBody: $value, attributes: $this->attributes,
                session: $this->session, protocolVersion: $this->protocolVersion,
            ),
            'requestTarget' => new self(
                body: $this->body, method: $this->method, uri: $this->uri,
                requestHeaders: $this->requestHeaders, serverParams: $this->serverParams,
                requestTarget: $value, cookies: $this->cookies,
                queryParams: $this->queryParams, uploadedFiles: $this->uploadedFiles,
                parsedBody: $this->parsedBody, attributes: $this->attributes,
                session: $this->session, protocolVersion: $this->protocolVersion,
            ),
            'protocolVersion' => new self(
                body: $this->body, method: $this->method, uri: $this->uri,
                requestHeaders: $this->requestHeaders, serverParams: $this->serverParams,
                requestTarget: $this->requestTarget, cookies: $this->cookies,
                queryParams: $this->queryParams, uploadedFiles: $this->uploadedFiles,
                parsedBody: $this->parsedBody, attributes: $this->attributes,
                session: $this->session, protocolVersion: $value,
            ),
            'requestHeaders' => new self(
                body: $this->body, method: $this->method, uri: $this->uri,
                requestHeaders: $value, serverParams: $this->serverParams,
                requestTarget: $this->requestTarget, cookies: $this->cookies,
                queryParams: $this->queryParams, uploadedFiles: $this->uploadedFiles,
                parsedBody: $this->parsedBody, attributes: $this->attributes,
                session: $this->session, protocolVersion: $this->protocolVersion,
            ),
            'body' => new self(
                body: $value, method: $this->method, uri: $this->uri,
                requestHeaders: $this->requestHeaders, serverParams: $this->serverParams,
                requestTarget: $this->requestTarget, cookies: $this->cookies,
                queryParams: $this->queryParams, uploadedFiles: $this->uploadedFiles,
                parsedBody: $this->parsedBody, attributes: $this->attributes,
                session: $this->session, protocolVersion: $this->protocolVersion,
            ),
            'session' => new self(
                body: $this->body, method: $this->method, uri: $this->uri,
                requestHeaders: $this->requestHeaders, serverParams: $this->serverParams,
                requestTarget: $this->requestTarget, cookies: $this->cookies,
                queryParams: $this->queryParams, uploadedFiles: $this->uploadedFiles,
                parsedBody: $this->parsedBody, attributes: $this->attributes,
                session: $value, protocolVersion: $this->protocolVersion,
            ),
            'attributes' => new self(
                body: $this->body, method: $this->method, uri: $this->uri,
                requestHeaders: $this->requestHeaders, serverParams: $this->serverParams,
                requestTarget: $this->requestTarget, cookies: $this->cookies,
                queryParams: $this->queryParams, uploadedFiles: $this->uploadedFiles,
                parsedBody: $this->parsedBody, attributes: $value,
                session: $this->session, protocolVersion: $this->protocolVersion,
            ),
            'cookies' => new self(
                body: $this->body, method: $this->method, uri: $this->uri,
                requestHeaders: $this->requestHeaders, serverParams: $this->serverParams,
                requestTarget: $this->requestTarget, cookies: $value,
                queryParams: $this->queryParams, uploadedFiles: $this->uploadedFiles,
                parsedBody: $this->parsedBody, attributes: $this->attributes,
                session: $this->session, protocolVersion: $this->protocolVersion,
            ),
            'uploadedFiles' => new self(
                body: $this->body, method: $this->method, uri: $this->uri,
                requestHeaders: $this->requestHeaders, serverParams: $this->serverParams,
                requestTarget: $this->requestTarget, cookies: $this->cookies,
                queryParams: $this->queryParams, uploadedFiles: $value,
                parsedBody: $this->parsedBody, attributes: $this->attributes,
                session: $this->session, protocolVersion: $this->protocolVersion,
            ),
        };
    }

    public function withMethod(string $method): self

    {
        return $this->with(prop: 'method', value: $method);
    }

    public function withUri(UriInterface $uri): self
    {
        return $this->with(prop: 'uri', value: $uri);
    }

    public function withQueryParams(array $queryParams): self
    {
        return $this->with(prop: 'queryParams', value: $queryParams);
    }

    public function withParsedBody(ParsedBody $parsedBody): self
    {
        return $this->with(prop: 'parsedBody', value: $parsedBody);
    }

    public function withRequestTarget(string $requestTarget): self
    {
        return $this->with(prop: 'requestTarget', value: $requestTarget);
    }

    public function withProtocolVersion(string $protocolVersion): self
    {
        return $this->with(prop: 'protocolVersion', value: $protocolVersion);
    }

    public function withRequestHeaders(#[\SensitiveParameter] RequestHeaders $requestHeaders): self
    {
        return $this->with(prop: 'requestHeaders', value: $requestHeaders);
    }

    public function withBody(RequestBody $body): self
    {
        return $this->with(prop: 'body', value: $body);
    }

    public function withSession(#[\SensitiveParameter] RequestSession $session): self
    {
        return $this->with(prop: 'session', value: $session);
    }

    public function withAttributes(RequestAttributes $attributes): self
    {
        return $this->with(prop: 'attributes', value: $attributes);
    }

    public function withCookies(RequestCookies $cookies): self
    {
        return $this->with(prop: 'cookies', value: $cookies);
    }

    public function withUploadedFiles(UploadedFiles $uploadedFiles): self
    {
        return $this->with(prop: 'uploadedFiles', value: $uploadedFiles);
    }

    private static function normalizeProtocolVersion(string $version): string
    {
        return match ($version) {
            '1.0', '1.1', '2.0' => $version,
            default => '1.1',
        };
    }

    private static function createUriFromServer(array $server): UriBuilder
    {
        $protocol = empty($server['HTTPS']) || $server['HTTPS'] === 'off' ? 'http' : 'https';
        $host = $server['HTTP_HOST'] ?? 'localhost';
        $uri = $server['REQUEST_URI'] ?? '/';

        return UriBuilder::createFromString(uri: "{$protocol}://{$host}{$uri}");
    }

    private static function extractHeaders(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $name = str_replace('_', '-', $key);
                $headers[$name] = $value;
            }
        }

        return $headers;
    }
}
