<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Configuration;

final readonly class WebhooksConfiguration
{
    public function __construct(
        public string $secret = '',
        public int    $retryAttempts = 3,
        public int    $retryBackoffMs = 1000,
        public int    $timeout = 10,
    ) {}
}
