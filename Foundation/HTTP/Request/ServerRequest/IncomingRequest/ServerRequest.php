<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\ParsedBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestCookies\RequestCookies;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\RequestedInputs;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestSession\RequestSession;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerEnvironment\ServerEnvironment;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\GuardUploadedFiles;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\UploadedFiles;
use InvalidArgumentException;
use NoDiscard;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Fluent API wrapper around RequestInit.
 *
 * This class is just a thin layer that delegates to RequestInit.
 * All state and logic is in RequestInit.
 */
final class ServerRequest implements ServerRequestInterface
{
    public UriInterface|null $uri {
        get => $this->init->uri;
    }

    public array $serverParams {
        get => $this->init->serverParams;
    }

    public array $queryParams {
        get => $this->init->queryParams;
    }

    public string|null $requestTarget {
        get => $this->init->requestTarget;
    }

    public string $method {
        get => $this->init->method;
    }

    public string $protocolVersion {
        get => $this->init->protocolVersion;
    }

    public array $headers {
        get => $this->init->headers;
    }

    public function __construct(private readonly RequestInit $init) {}

    public static function create(RequestInit $init): self
    {
        return new self(init: $init);
    }

    private static function requireString(mixed $value, string $argumentName): string
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException(message: sprintf('%s must be a string.', $argumentName));
        }

        return $value;
    }

    public function getUri(): UriInterface
    {
        return $this->init->uri;
    }

    public function getServerParams(): array
    {
        return $this->init->serverParams;
    }

    public function getQueryParams(): array
    {
        return $this->init->queryParams;
    }

    public function getParsedBody(): array|object|null
    {
        return $this->init->parsedBody->data();
    }

    public function getCookieParams(): array
    {
        return $this->init->cookies->all();
    }

    public function getUploadedFiles(): array
    {
        return $this->init->uploadedFiles->all();
    }

    public function getAttributes(): array
    {
        return $this->init->attributes->all();
    }

    public function getAttribute($name, $default = null): mixed
    {
        return $this->init->attributes->get(
            name: self::requireString(value: $name, argumentName: 'Attribute name'),
            default: $default,
        );
    }

    public function getMethod(): string
    {
        return $this->init->method;
    }

    public function getRequestTarget(): string
    {
        return $this->init->requestTarget ?? '';
    }

    public function getProtocolVersion(): string
    {
        return $this->init->protocolVersion;
    }

    public function getHeaders(): array
    {
        return $this->init->headers;
    }

    public function hasHeader($name): bool
    {
        return $this->init->requestHeaders->has(name: self::requireString(value: $name, argumentName: 'Header name'));
    }

    public function getHeader($name): array
    {
        return $this->init->requestHeaders->get(name: self::requireString(value: $name, argumentName: 'Header name'));
    }

    public function getHeaderLine($name): string
    {
        return $this->init->requestHeaders->getLine(name: self::requireString(value: $name, argumentName: 'Header name'));
    }

    public function getBody(): StreamInterface
    {
        return $this->init->body->stream();
    }

    public function inputs(): RequestedInputs
    {
        return new RequestedInputs(
            queryParams: $this->init->queryParams,
            parsedBody: $this->getParsedBody(),
        );
    }

    public function serverEnvironment(): ServerEnvironment
    {
        return ServerEnvironment::fromServerParams(serverParams: $this->init->serverParams);
    }

    public function requestSession(): RequestSession|null
    {
        return $this->init->session;
    }

    #[NoDiscard]
    public function withProtocolVersion($version): self
    {
        return new self(init: $this->init->withProtocolVersion(protocolVersion: $version));
    }

    #[NoDiscard]
    public function withRequestTarget($requestTarget): self
    {
        return new self(init: $this->init->withRequestTarget(
            requestTarget: self::requireString(value: $requestTarget, argumentName: 'Request target')
        ));
    }

    #[NoDiscard]
    public function withMethod($method): self
    {
        return new self(init: $this->init->withMethod(method: $method));
    }

    #[NoDiscard]
    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $init = $this->init->withUri(uri: $uri);

        $host = $uri->getHost();
        if ($host === '') {
            return new self(init: $init);
        }

        if ($preserveHost && $this->hasHeader(name: 'Host')) {
            return new self(init: $init);
        }

        $port = $uri->getPort();
        $hostHeader = $port === null
            ? $host
            : sprintf('%s:%d', $host, $port);

        $init = $init->withRequestHeaders(
            requestHeaders: $this->init->requestHeaders->put(name: 'Host', value: $hostHeader)
        );

        return new self(init: $init);
    }

    #[NoDiscard]
    public function withHeader($name, $value): self
    {
        return new self(init: $this->init->withRequestHeaders(
            requestHeaders: $this->init->requestHeaders->put(name: self::requireString(value: $name, argumentName: 'Header name'), value: $value)
        ));
    }

    #[NoDiscard]
    public function withAddedHeader($name, $value): self
    {
        return new self(init: $this->init->withRequestHeaders(
            requestHeaders: $this->init->requestHeaders->append(name: self::requireString(value: $name, argumentName: 'Header name'), value: $value)
        ));
    }

    #[NoDiscard]
    public function withoutHeader($name): self
    {
        return new self(init: $this->init->withRequestHeaders(
            requestHeaders: $this->init->requestHeaders->drop(name: self::requireString(value: $name, argumentName: 'Header name'))
        ));
    }

    #[NoDiscard]
    public function withBody(StreamInterface $body): self
    {
        return new self(init: $this->init->withBody(body: new RequestBody(stream: $body)));
    }

    #[NoDiscard]
    public function withQueryParams(array $query): self
    {
        return new self(init: $this->init->withQueryParams(queryParams: $query));
    }

    #[NoDiscard]
    public function withCookieParams(array $cookies): self
    {
        return new self(init: $this->init->withCookies(cookies: new RequestCookies(cookies: $cookies)));
    }

    #[NoDiscard]
    public function withUploadedFiles(array $uploadedFiles): self
    {
        (new GuardUploadedFiles)->execute(files: $uploadedFiles);

        return new self(init: $this->init->withUploadedFiles(uploadedFiles: new UploadedFiles(files: $uploadedFiles)));
    }

    #[NoDiscard]
    public function withParsedBody($data): self
    {
        if ($data !== null && ! is_array($data) && ! is_object($data)) {
            throw new InvalidArgumentException(
                message: 'Parsed body must be null, an array, or an object.',
            );
        }

        return new self(init: $this->init->withParsedBody(parsedBody: new ParsedBody(data: $data)));
    }

    #[NoDiscard]
    public function withAttribute($name, $value): self
    {
        return new self(init: $this->init->withAttributes(
            attributes: $this->init->attributes->put(
                name: self::requireString(value: $name, argumentName: 'Attribute name'),
                value: $value,
            )
        ));
    }

    #[NoDiscard]
    public function withoutAttribute($name): self
    {
        return new self(init: $this->init->withAttributes(
            attributes: $this->init->attributes->drop(
                name: self::requireString(value: $name, argumentName: 'Attribute name'),
            )
        ));
    }
}
