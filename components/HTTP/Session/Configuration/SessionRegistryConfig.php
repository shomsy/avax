<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\Configuration;

final class SessionRegistryConfig
{
    public function __construct(
        public readonly bool $enabled = false,
        public readonly int  $maxSessions = 5
    ) {}
}