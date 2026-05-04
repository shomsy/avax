<?php

declare(strict_types=1);

namespace Avax\Components\Server\System\Configuration;

final readonly class ServerConfig
{
    public function __construct(
        public string $host = '0.0.0.0',
        public int    $port = 8080,
    )
    {
    }
}
