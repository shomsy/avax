<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Capabilities\ResponseData;

use Psr\Http\Message\StreamInterface;

final readonly class ResponseData
{
    private function __construct(
        public int             $statusCode,
        public array           $headers,
        public StreamInterface $body,
        public string          $reasonPhrase,
        public string          $protocolVersion,
    ) {}

    /**
     * @param array<string, mixed> $headers
     */
    public static function create(
        int             $statusCode,
        array           $headers,
        StreamInterface $body,
        string          $reasonPhrase,
        string          $protocolVersion,
    ) : self
    {
        $normalized = [];
        foreach ($headers as $name => $values) {
            $normalized[strtolower($name)] = $values;
        }

        return new self(
            statusCode     : $statusCode,
            headers        : $normalized,
            body           : $body,
            reasonPhrase   : $reasonPhrase,
            protocolVersion: $protocolVersion,
        );
    }

    public function withProtocolVersion(string $version) : self
    {
        return new self(
            statusCode     : $this->statusCode,
            headers        : $this->headers,
            body           : $this->body,
            reasonPhrase   : $this->reasonPhrase,
            protocolVersion: $version,
        );
    }

    public function withHeader(string $name, array $values) : self
    {
        $headers                    = $this->headers;
        $headers[strtolower($name)] = $values;

        return new self(
            statusCode     : $this->statusCode,
            headers        : $headers,
            body           : $this->body,
            reasonPhrase   : $this->reasonPhrase,
            protocolVersion: $this->protocolVersion,
        );
    }

    public function withoutHeader(string $name) : self
    {
        $headers = $this->headers;
        unset($headers[strtolower($name)]);

        return new self(
            statusCode     : $this->statusCode,
            headers        : $headers,
            body           : $this->body,
            reasonPhrase   : $this->reasonPhrase,
            protocolVersion: $this->protocolVersion,
        );
    }

    public function withBody(StreamInterface $stream) : self
    {
        return new self(
            statusCode     : $this->statusCode,
            headers        : $this->headers,
            body           : $stream,
            reasonPhrase   : $this->reasonPhrase,
            protocolVersion: $this->protocolVersion,
        );
    }

    public function withStatus(int $code, string $reasonPhrase) : self
    {
        return new self(
            statusCode     : $code,
            headers        : $this->headers,
            body           : $this->body,
            reasonPhrase   : $reasonPhrase,
            protocolVersion: $this->protocolVersion,
        );
    }
}
