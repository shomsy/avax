<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\IncomingRequest;

use Avax\Components\HTTP\Request\System\System\PublicSurface\RequestInterface;
use GuzzleHttp\Psr7\UploadedFile;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * Server-side HTTP request implementation.
 *
 * Wraps PHP superglobals ($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES)
 * and provides PSR-7 compatible access.
 */
class ServerRequest implements RequestInterface, ServerRequestInterface
{
    private array $attributes = [];

    private array $headers = [];

    private StreamInterface $stream;

    private ?UriInterface $uri = null;

    private ?string $requestTarget = null;

    private string $method = 'GET';

    public function __construct(
        private array $serverParams = [],
        private array $cookieParams = [],
        private array $queryParams = [],
        private array $uploadedFiles = [],
        private ?array $parsedBody = null,
        string $method = 'GET',
        UriInterface|string|null $uri = null,
        private string $protocolVersion = '1.1',
        array $headers = [],
        ?StreamInterface $stream = null,
    ) {
        $this->method = strtoupper($method);
        $this->stream = $stream ?? Utils::streamFor('');
        $this->headers = $this->normalizeHeaders($headers);

        if ($uri !== null) {
            $this->uri = is_string($uri) ? $this->createUriFromString($uri) : $uri;
        }
    }

    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $values = is_array(value: $value) ? $value : [$value];
            $normalized[strtolower(string: (string) $name)] = array_map(
                callback: static fn (mixed $headerValue): string => (string) $headerValue,
                array   : array_values(array: $values),
            );
        }

        return $normalized;
    }

    private function createUriFromString(string $uri): UriInterface
    {
        $parts = parse_url($uri);

        return new readonly class ($parts['scheme'] ?? '', $parts['host'] ?? '', $parts['port'] ?? null, $parts['path'] ?? '', $parts['query'] ?? '', $parts['fragment'] ?? '', $parts['user'] ?? '') implements UriInterface {
            public function __construct(
                private string $scheme,
                private string $host,
                private ?int $port,
                private string $path,
                private string $query,
                private string $fragment,
                private string $user,
            ) {
            }

            public function getScheme(): string
            {
                return $this->scheme;
            }

            public function getAuthority(): string
            {
                $authority = $this->host;
                if ($this->port !== null) {
                    $authority .= ':'.$this->port;
                }

                return $authority;
            }

            public function getUserInfo(): string
            {
                return $this->user;
            }

            public function getHost(): string
            {
                return $this->host;
            }

            public function getPort(): ?int
            {
                return $this->port;
            }

            public function getPath(): string
            {
                return $this->path;
            }

            public function getQuery(): string
            {
                return $this->query;
            }

            public function getFragment(): string
            {
                return $this->fragment;
            }

            public function withScheme($scheme): self
            {
                return new self($scheme, $this->host, $this->port, $this->path, $this->query, $this->fragment, $this->user);
            }

            public function withUserInfo($user, $password = null): self
            {
                return new self($this->scheme, $this->host, $this->port, $this->path, $this->query, $this->fragment, $user);
            }

            public function withHost($host): self
            {
                return new self($this->scheme, $host, $this->port, $this->path, $this->query, $this->fragment, $this->user);
            }

            public function withPort($port): self
            {
                return new self($this->scheme, $this->host, $port, $this->path, $this->query, $this->fragment, $this->user);
            }

            public function withPath($path): self
            {
                return new self($this->scheme, $this->host, $this->port, $path, $this->query, $this->fragment, $this->user);
            }

            public function withQuery($query): self
            {
                return new self($this->scheme, $this->host, $this->port, $this->path, $query, $this->fragment, $this->user);
            }

            public function withFragment($fragment): self
            {
                return new self($this->scheme, $this->host, $this->port, $this->path, $this->query, $fragment, $this->user);
            }

            public function __toString(): string
            {
                $uri = $this->scheme.'://'.$this->getAuthority().$this->path;
                if ($this->query !== '') {
                    $uri .= '?'.$this->query;
                }

                if ($this->fragment !== '') {
                    $uri .= '#'.$this->fragment;
                }

                return $uri;
            }
        };
    }

    /**
     * Create a ServerRequest from PHP superglobals.
     */
    public static function fromGlobals(): self
    {
        return new self(
            serverParams : $_SERVER,
            cookieParams : $_COOKIE,
            queryParams  : $_GET,
            uploadedFiles: self::normalizeFiles($_FILES),
            parsedBody   : $_POST,
            method       : $_SERVER['REQUEST_METHOD'] ?? 'GET',
            headers      : function_exists('getallheaders') ? getallheaders() : [],
            stream       : Utils::streamFor(file_get_contents('php://input')),
        );
    }

    private static function normalizeFiles(array $files): array
    {
        $normalized = [];
        foreach ($files as $key => $value) {
            $normalized[$key] = self::createUploadedFile($value);
        }

        return $normalized;
    }

    private static function createUploadedFile(array $file): UploadedFileInterface
    {
        $tmpName = $file['tmp_name'] ?? '';
        $streamOrFile = is_string(value: $tmpName) && $tmpName !== ''
            ? $tmpName
            : Utils::streamFor('');
        $clientFilename = isset($file['name']) && is_string(value: $file['name']) && $file['name'] !== ''
            ? $file['name']
            : null;
        $clientMediaType = isset($file['type']) && is_string(value: $file['type']) && $file['type'] !== ''
            ? $file['type']
            : null;

        return new UploadedFile(
            $streamOrFile,
            (int) ($file['size'] ?? 0),
            (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE),
            $clientFilename,
            $clientMediaType,
        );
    }

    // PSR-7 RequestInterface methods

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version): self
    {
        $new = clone $this;
        $new->protocolVersion = $version;

        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        return isset($this->headers[strtolower(string: (string) $name)]);
    }

    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function getHeader($name): array
    {
        return $this->headers[strtolower(string: (string) $name)] ?? [];
    }

    public function withHeader($name, $value): self
    {
        $new = clone $this;
        $new->headers[strtolower(string: (string) $name)] = is_array(value: $value) ? $value : [$value];

        return $new;
    }

    public function withAddedHeader($name, $value): self
    {
        $new = clone $this;
        $existing = $this->getHeader($name);
        $new->headers[strtolower(string: (string) $name)] = array_merge(
            $existing,
            is_array(value: $value) ? $value : [$value],
        );

        return $new;
    }

    public function withoutHeader($name): self
    {
        $new = clone $this;
        unset($new->headers[strtolower(string: (string) $name)]);

        return $new;
    }

    public function getBody(): StreamInterface
    {
        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        return Utils::streamFor($this->stream ?? '');
    }

    public function withBody(StreamInterface $body): self
    {
        $new = clone $this;
        $new->stream = $body;

        return $new;
    }

    // PSR-7 ServerRequestInterface methods
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): self
    {
        $new = clone $this;
        $new->cookieParams = $cookies;

        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): self
    {
        $new = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): self
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    public function getParsedBody(): ?array
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data): self
    {
        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute($name, $value): self
    {
        $new = clone $this;
        $new->attributes[$name] = $value;

        return $new;
    }

    public function withoutAttribute($name): self
    {
        $new = clone $this;
        unset($new->attributes[$name]);

        return $new;
    }

    public function getRequestTarget(): string
    {
        return $this->requestTarget ?? $this->uri?->getPath() ?? '/';
    }

    public function withRequestTarget($requestTarget): self
    {
        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod($method): self
    {
        $new = clone $this;
        $new->method = strtoupper($method);

        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri ?? $this->createUriFromString('');
    }

    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $new = clone $this;
        $new->uri = $uri;

        return $new;
    }

    // RequestInterface (Avax) methods
    public function input(string $key, mixed $default = null): mixed
    {
        $body = $this->parsedBody ?? [];
        $query = $this->queryParams;

        return $body[$key] ?? $query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->queryParams, $this->parsedBody ?? []);
    }
}
