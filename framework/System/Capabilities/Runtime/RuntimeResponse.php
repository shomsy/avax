<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Psr\Http\Message\ResponseInterface;

final readonly class RuntimeResponse
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        private int $statusCode,
        private array $headers = [],
        private string $body = '',
    ) {
        if ($this->statusCode < 100 || $this->statusCode > 599) {
            throw new FrameworkMisconfigured(
                message: sprintf('Runtime response status "%d" is invalid.', $this->statusCode),
            );
        }
    }

    public static function fromPsrResponse(ResponseInterface $response) : self
    {
        return new self(
            statusCode: $response->getStatusCode(),
            headers   : $response->getHeaders(),
            body      : (string) $response->getBody(),
        );
    }

    public function statusCode() : int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, list<string>>
     */
    public function headers() : array
    {
        return $this->headers;
    }

    public function body() : string
    {
        return $this->body;
    }
}
