<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\System\Capabilities\Http;

final readonly class ClientRequest
{
    public function __construct(
        public string  $method,
        public string  $url,
        public array   $headers = [],
        public ?string $body = null,
        public array   $options = [],
    ) {}
}
