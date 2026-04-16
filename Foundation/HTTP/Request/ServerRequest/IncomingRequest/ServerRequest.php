<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestAttributes\RequestAttributes;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\ParsedBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestCookies\RequestCookies;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders\RequestHeaders;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestSession\RequestSession;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerEnvironment\ServerEnvironment;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\Inputs\DTO\InputsDTO;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\RequestedInputs;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\GuardUploadedFiles;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\UploadedFiles;
use Avax\HTTP\URI\UriBuilder;
use InvalidArgumentException;
use NoDiscard;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use SensitiveParameter;

/**
 * State owner: clean, immutable PSR-7 server request.
 *
 * It stores request state and delegates semantic behavior
 * to dedicated capability owners.
 */
final class ServerRequest implements ServerRequestInterface
{
    /** @var array<string, mixed> */
    public array $serverParams {
        get {
            return $this->serverParams;
        }
    }

    /** @var array<string, mixed> */
    public array $queryParams {
        get {
            return $this->queryParams;
        }
    }

    public string|null          $requestTarget {
        get {
            return $this->requestTarget;
        }
    }
    private RequestSession|null $session;

    public string $method {
        get {
            return $this->method;
        }
    }
    public string $protocolVersion {
        get {
            return $this->protocolVersion;
        }
    }

    public array $headers {
        get {
            return $this->requestHeaders->all();
        }
    }

    public function __construct(
        private readonly RequestBody $body,
        string|null                  $method = null,
        public UriInterface|null     $uri = null {
            get {
                return $this->uri;
            }
        },
        #[SensitiveParameter]
        public RequestHeaders|null $requestHeaders = null,
        array|null                   $serverParams = null,
        string|null                  $requestTarget = null,
        private RequestCookies|null    $cookies = null,
        array|null                     $queryParams = null,
        private UploadedFiles|null     $uploadedFiles = null,
        private ParsedBody|null        $parsedBody = null,
        private RequestAttributes|null $attributes = null,
        #[SensitiveParameter]
        RequestSession|null            $session = null,
        string                         $protocolVersion = '1.1'
    ) {
        $serverParams           ??= [];
        $method                  = $method ?? ($serverParams['REQUEST_METHOD'] ?? 'GET');
        $this->uri               ??= new UriBuilder(scheme: '');
        $this->requestHeaders   ??= new RequestHeaders();
        $this->cookies          ??= new RequestCookies();
        $this->uploadedFiles    ??= new UploadedFiles();
        $this->parsedBody       ??= new ParsedBody();
        $this->attributes       ??= new RequestAttributes();
        $this->method           = self::normalizeMethod(method: $method);
        $this->protocolVersion  = self::normalizeProtocolVersion(version: $protocolVersion);
        $this->serverParams     = $serverParams;
        $this->requestTarget    = $requestTarget;
        $this->queryParams      = $queryParams ?? [];
        $this->session          = $session;
    }

    #[NoDiscard]
    public function withProtocolVersion($version): self
    {
        $normalized = self::normalizeProtocolVersion(version: $version);

        if ($normalized === $this->protocolVersion) {
            return $this;
        }

        return clone(object: $this, withProperties: [
            "protocolVersion" => $normalized
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getHeaders(): array { return $this->requestHeaders->all(); }

    public function hasHeader($name): bool
    {
        return $this->requestHeaders->has(name: self::requireString(value: $name, argumentName: 'Header name'));
    }

    /**
     * @return array<int, string>
     */
    public function getHeader($name): array
    {
        return $this->requestHeaders->get(name: self::requireString(value: $name, argumentName: 'Header name'));
    }

    public function getHeaderLine($name): string
    {
        return $this->requestHeaders->getLine(name: self::requireString(value: $name, argumentName: 'Header name'));
    }

    #[NoDiscard]
    public function withHeader($name, $value): self
    {
        return clone(object: $this, withProperties: [
            "requestHeaders" => $this->requestHeaders->put(name: self::requireString(value: $name, argumentName: 'Header name'), value: $value)
        ]);
    }

    #[NoDiscard]
    public function withAddedHeader($name, $value): self
    {
        return clone(object: $this, withProperties: [
            "requestHeaders" => $this->requestHeaders->append(name: self::requireString(value: $name, argumentName: 'Header name'), value: $value)
        ]);
    }

    #[NoDiscard]
    public function withoutHeader($name): self
    {
        return clone(object: $this, withProperties: [
            "requestHeaders" => $this->requestHeaders->drop(name: self::requireString(value: $name, argumentName: 'Header name'))
        ]);
    }

    public function getBody(): StreamInterface { return $this->body->stream(); }

    #[NoDiscard]
    public function withBody(StreamInterface $body): self
    {
        return clone(object: $this, withProperties: [
            "body" => new RequestBody(stream: $body)
        ]);
    }

    #[NoDiscard]
    public function withRequestTarget($requestTarget): self
    {
        return clone(object: $this, withProperties: [
            "requestTarget" => self::requireString(value: $requestTarget, argumentName: 'Request target')
        ]);
    }

    #[NoDiscard]
    public function withMethod($method): self
    {
        $normalized = self::normalizeMethod(method: $method);

        if ($normalized === $this->method) {
            return $this;
        }

        return clone(object: $this, withProperties: [
            "method" => $normalized
        ]);
    }

    #[NoDiscard]
    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $new = clone(object: $this, withProperties: [
            "uri" => $uri
        ]);

        $host = $uri->getHost();
        if ($host === '') {
            return $new;
        }

        if ($preserveHost && $this->hasHeader(name: 'Host')) {
            return $new;
        }

        $port = $uri->getPort();
        $hostHeader = $port === null
            ? $host
            : sprintf('%s:%d', $host, $port);

        $new->requestHeaders = $new->requestHeaders->put(name: 'Host', value: $hostHeader);
        return $new;
    }

    /**
     * @return array<string, string>
     */
    public function getCookieParams(): array { return $this->cookies->all(); }

    #[NoDiscard]
    public function withCookieParams(array $cookies): self
    {
        return clone(object: $this, withProperties: [
            "cookies" => $this->cookies->with(cookies: $cookies)
        ]);
    }

    #[NoDiscard]
    public function withQueryParams(array $query): self
    {
        return clone(object: $this, withProperties: [
            "queryParams" => $query
        ]);
    }

    public function getUploadedFiles(): array { return $this->uploadedFiles->all(); }

    #[NoDiscard]
    public function withUploadedFiles(array $uploadedFiles): self
    {
        new GuardUploadedFiles()->execute(files: $uploadedFiles);

        return clone(object: $this, withProperties: [
            "uploadedFiles" => $this->uploadedFiles->with(files: $uploadedFiles)
        ]);
    }

    public function getParsedBody(): array|object|null { return $this->parsedBody->data(); }

    #[NoDiscard]
    public function withParsedBody($data): self
    {
        if ($data !== null && !is_array($data) && !is_object($data)) {
            throw new InvalidArgumentException(
                message: 'Parsed body must be null, an array, or an object.'
            );
        }

        return clone(object: $this, withProperties: [
            "parsedBody" => $this->parsedBody->with(data: $data)
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array { return $this->attributes->all(); }

    public function getAttribute($name, $default = null): mixed
    {
        return $this->attributes->get(
            name: self::requireString(value: $name, argumentName: 'Attribute name'),
            default: $default,
        );
    }

    #[NoDiscard]
    public function withAttribute($name, $value): self
    {
        return clone(object: $this, withProperties: [
            "attributes" => $this->attributes->put(
                name : self::requireString(value: $name, argumentName: 'Attribute name'),
                value: $value,
            )
        ]);
    }

    #[NoDiscard]
    public function withoutAttribute($name): self
    {
        return clone(object: $this, withProperties: [
            "attributes" => $this->attributes->drop(
                name: self::requireString(value: $name, argumentName: 'Attribute name'),
            )
        ]);
    }

    public function inputs(): RequestedInputs
    {
        $dto = InputsDTO::fromSlices(
            queryParams: $this->queryParams,
            parsedBody: $this->getParsedBody()
        );

        return new RequestedInputs(dto: $dto);
    }

    public function serverEnvironment(): ServerEnvironment
    {
        return ServerEnvironment::fromServerParams(serverParams: $this->serverParams);
    }

    public function requestSession(): RequestSession|null { return $this->session; }

    private static function normalizeProtocolVersion(mixed $version): string
    {
        return new NormalizeProtocolVersion()->execute(
            protocol: self::requireString(value: $version, argumentName: 'Protocol version'),
        );
    }

    private static function normalizeMethod(mixed $method): string
    {
        $method = strtoupper(trim(self::requireString(value: $method, argumentName: 'HTTP method')));

        if ($method === '') {
            throw new InvalidArgumentException(message: 'HTTP method cannot be empty.');
        }

        return $method;
    }

    private static function requireString(mixed $value, string $argumentName): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(message: sprintf('%s must be a string.', $argumentName));
        }

        return $value;
    }
}