<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Requests;

/**
 * OutboundRequest - Value object representing an outbound HTTP request.
 *
 * Immutable value object that captures all aspects of an HTTP request:
 * method, URL, headers, body, and options.
 */
final readonly class OutboundRequest
{
    /**
     * @param string               $method  HTTP method (GET, POST, etc.)
     * @param string               $url     Full URL or path
     * @param mixed                $body    Request body (string, array, null)
     * @param array<string, string> $headers HTTP headers
     * @param RequestOptions|null  $options Request options
     * @param array<string, mixed> $context Additional context for middleware
     */
    public function __construct(
        public string $method = 'GET',
        public string $url = '',
        public mixed  $body = null,
        public array  $headers = [],
        public RequestOptions|null $options = null,
        public array  $context = [],
    ) {}

    /**
     * Create a new instance with a different method.
     */
    public function withMethod(string $method) : self
    {
        return new self(
            method : $method,
            url    : $this->url,
            body   : $this->body,
            headers: $this->headers,
            options: $this->options,
            context: $this->context,
        );
    }

    /**
     * Create a new instance with a different URL.
     */
    public function withUrl(string $url) : self
    {
        return new self(
            method : $this->method,
            url    : $url,
            body   : $this->body,
            headers: $this->headers,
            options: $this->options,
            context: $this->context,
        );
    }

    /**
     * Create a new instance with a different body.
     */
    public function withBody(mixed $body) : self
    {
        return new self(
            method : $this->method,
            url    : $this->url,
            body   : $body,
            headers: $this->headers,
            options: $this->options,
            context: $this->context,
        );
    }

    /**
     * Create a new instance with additional headers.
     *
     * @param array<string, string> $headers Headers to add/merge
     */
    public function withHeaders(array $headers) : self
    {
        return new self(
            method : $this->method,
            url    : $this->url,
            body   : $this->body,
            headers: array_merge($this->headers, $headers),
            options: $this->options,
            context: $this->context,
        );
    }

    /**
     * Create a new instance with different options.
     */
    public function withOptions(RequestOptions $options) : self
    {
        return new self(
            method : $this->method,
            url    : $this->url,
            body   : $this->body,
            headers: $this->headers,
            options: $options,
            context: $this->context,
        );
    }

    /**
     * Create a new instance with additional context.
     *
     * @param array<string, mixed> $context Context to merge
     */
    public function withContext(array $context) : self
    {
        return new self(
            method : $this->method,
            url    : $this->url,
            body   : $this->body,
            headers: $this->headers,
            options: $this->options,
            context: array_merge($this->context, $context),
        );
    }

    /**
     * Check if the request has a body.
     */
    public function hasBody() : bool
    {
        return $this->body !== null;
    }

    /**
     * Check if the request expects JSON.
     */
    public function expectsJson() : bool
    {
        $accept = $this->getHeader('Accept') ?? '';

        return str_contains($accept, 'application/json');
    }

    /**
     * Get a specific header value.
     */
    public function getHeader(string $name) : string|null
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Check if this is a safe method (no side effects).
     */
    public function isSafe() : bool
    {
        return in_array(strtoupper($this->method), ['GET', 'HEAD', 'OPTIONS'], true);
    }

    /**
     * Check if this is an idempotent method.
     */
    public function isIdempotent() : bool
    {
        return in_array(strtoupper($this->method), ['GET', 'HEAD', 'OPTIONS', 'PUT', 'DELETE'], true);
    }
}
