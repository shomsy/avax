<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\System\Capabilities\Http;

final readonly class ClientResponse
{
    public function __construct(
        public int    $statusCode,
        public array  $headers,
        public string $body,
    ) {}

    public function json() : array
    {
        return json_decode($this->body, true) ?: [];
    }
}
