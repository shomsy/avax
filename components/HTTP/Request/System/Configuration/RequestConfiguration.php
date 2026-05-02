<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Configuration;

final readonly class RequestConfiguration
{
    public function __construct(
        public array $trustedProxies = [],
    ) {}
}
