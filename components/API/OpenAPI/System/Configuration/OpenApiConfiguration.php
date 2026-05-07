<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\Configuration;

final readonly class OpenApiConfiguration
{
    public function __construct(
        public string $title = 'AvaX API',
        public string $version = '1.0.0',
        public string $baseUrl = '/',
    ) {}
}
