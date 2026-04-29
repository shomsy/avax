<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\PublicSurface;

use Avax\Components\HTTP\Request\System\Capabilities\Headers\RequestHeaders;
use Avax\Components\HTTP\Request\System\Capabilities\Body\RequestBody;
use Avax\Components\HTTP\Request\System\Capabilities\Files\UploadedFiles;
use Avax\Components\HTTP\Request\System\Capabilities\Uri\RequestUri;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class Request implements RequestInterface
{
    public function __construct(
        private string $method,
        private RequestUri $uri,
        private RequestHeaders $headers,
        private RequestBody $body,
        private UploadedFiles $files,
        private array $serverParams = [],
        private array $cookieParams = [],
        private array $queryParams = [],
        private array $attributes = [],
        private string $protocolVersion = '1.1'
    ) {}

    public function getProtocolVersion(): string { return $this->protocolVersion; }
    public function withProtocolVersion($version): self { $clone = clone $this; $clone->protocolVersion = $version; return $clone; }

    public function getHeaders(): array { return $this->headers->all(); }
    public function hasHeader($name): bool { return $this->headers->has($name); }
    public function getHeader($name): array { return $this->headers->get($name)?->all() ?? []; }
    public function getHeaderLine($name): string { return $this->headers->get($name)?->line() ?? ''; }
    public function withHeader($name, $value): self { $clone = clone $this; $clone->headers->set($name, $value); return $clone; }
    public function withAddedHeader($name, $value): self { return $this->withHeader($name, $value); }
    public function withoutHeader($name): self { return $this; }

    public function getBody(): StreamInterface { return \GuzzleHttp\Psr7\Utils::streamFor($this->body->raw()->toString()); }
    public function withBody(StreamInterface $body): self { return $this; }

    public function getRequestTarget(): string { return $this->uri->getPath(); }
    public function withRequestTarget($target): self { return $this; }

    public function getMethod(): string { return $this->method; }
    public function withMethod($method): self { $clone = clone $this; $clone->method = $method; return $clone; }

    public function getUri(): UriInterface { return $this->uri; }
    public function withUri(UriInterface $uri, $preserveHost = false): self { $clone = clone $this; $clone->uri = $uri; return $clone; }

    public function getServerParams(): array { return $this->serverParams; }
    public function getCookieParams(): array { return $this->cookieParams; }
    public function withCookieParams(array $cookies): self { $clone = clone $this; $clone->cookieParams = $cookies; return $clone; }
    public function getQueryParams(): array { return $this->queryParams; }
    public function withQueryParams(array $query): self { $clone = clone $this; $clone->queryParams = $query; return $clone; }
    public function getUploadedFiles(): array { return $this->files->all(); }
    public function withUploadedFiles(array $uploadedFiles): self { return $this; }
    public function getParsedBody(): array|object|null { return $this->body->parsed()->data(); }
    public function withParsedBody($data): self { return $this; }
    public function getAttributes(): array { return $this->attributes; }
    public function getAttribute($name, $default = null) { return $this->attributes[$name] ?? $default; }
    public function withAttribute($name, $value): self { $clone = clone $this; $clone->attributes[$name] = $value; return $clone; }
    public function withoutAttribute($name): self { $clone = clone $this; unset($clone->attributes[$name]); return $clone; }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $this->queryParams[$key] ?? ($this->getParsedBody()[$key] ?? $default);
    }

    public function all(): array
    {
        return array_merge($this->queryParams, (array)$this->getParsedBody(), $this->attributes);
    }
}
