<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Configuration;

final class SessionRecoveryConfig
{
    public function __construct(
        public readonly bool $enabled = false,
        public readonly int  $maxSnapshots = 10
    ) {}
}