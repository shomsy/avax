<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Configuration;

use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use GuzzleHttp\Psr7\Utils;

/**
 * Builder for creating configured Response instances.
 *
 * Usage:
 *   $response = (new ResponseBuilder())
 *       ->withStatus(201)
 *       ->withHeader('Content-Type', 'application/json')
 *       ->withBody('{"id": 1}')
 *       ->build();
 */
final class ResponseBuilder
{
    private int $statusCode = 200;

    private string $reasonPhrase = '';

    /** @var array<string, list<string>> */
    private array $headers = [];

    private string|null $body = null;

    private string $protocolVersion = '1.1';

    /**
     * Set the HTTP status code.
     */
    public function withStatus(int $code, string $reasonPhrase = '') : self
    {
        $self               = clone $this;
        $self->statusCode   = $code;
        $self->reasonPhrase = $reasonPhrase;

        return $self;
    }

    /**
     * Add a header value (appends to existing values for the same header).
     */
    public function withHeader(string $name, string $value) : self
    {
        $self                = clone $this;
        $key                 = strtolower($name);
        $self->headers[$key] ??= [];
        $self->headers[$key][] = $value;

        return $self;
    }

    /**
     * Replace all values for a header.
     *
     * @param list<string> $values
     */
    public function withHeaders(string $name, array $values) : self
    {
        $self                             = clone $this;
        $self->headers[strtolower($name)] = $values;

        return $self;
    }

    /**
     * Set the response body as a string.
     */
    public function withBody(string $body) : self
    {
        $self       = clone $this;
        $self->body = $body;

        return $self;
    }

    /**
     * Set the HTTP protocol version.
     */
    public function withProtocolVersion(string $version) : self
    {
        $self                  = clone $this;
        $self->protocolVersion = $version;

        return $self;
    }

    /**
     * Convenience: build a JSON response.
     *
     * @param mixed $data
     * @param int $statusCode
     * @param int $jsonFlags
     */
    public function json(mixed $data, int $statusCode = 200, int $jsonFlags = JSON_THROW_ON_ERROR) : self
    {
        $self                          = clone $this;
        $self->statusCode              = $statusCode;
        $self->body                    = json_encode($data, $jsonFlags);
        $self->headers['content-type'] = ['application/json; charset=utf-8'];

        return $self;
    }

    /**
     * Build and return the Response instance.
     */
    public function build() : ResponseInterface
    {
        $response = new Response(
            statusCode     : $this->statusCode,
            headers        : $this->headers,
            body           : null,
            reasonPhrase   : $this->reasonPhrase,
            protocolVersion: $this->protocolVersion,
        );

        if ($this->body !== null) {
            $response = $response->withBody(Utils::streamFor($this->body));
        }

        return $response;
    }

    /**
     * Get the configured status code.
     */
    public function getStatusCode() : int
    {
        return $this->statusCode;
    }

    /**
     * Get all configured headers.
     *
     * @return array<string, list<string>>
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }
}
