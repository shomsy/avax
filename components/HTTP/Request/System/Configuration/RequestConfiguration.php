<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Configuration;

final class RequestConfiguration
{
    public function __construct(
        public readonly array $trustedProxies = [],
    ) {}
}
