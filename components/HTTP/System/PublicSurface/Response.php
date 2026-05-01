<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\PublicSurface;

final class Response
{
    public function __construct(
        private int $statusCode = 200,
        private array $headers = [],
        private string $body = '',
    ) {}

    public function statusCode() : int
    {
        return $this->statusCode;
    }

    public function headers() : array
    {
        return $this->headers;
    }

    public function body() : string
    {
        return $this->body;
    }

    public function withStatus(int $code) : self
    {
        return new self($code, $this->headers, $this->body);
    }

    public function withHeader(string $name, string $value) : self
    {
        $headers = $this->headers;
        $headers[$name] = $value;

        return new self($this->statusCode, $headers, $this->body);
    }

    public function withBody(string $body) : self
    {
        return new self($this->statusCode, $this->headers, $body);
    }
}
