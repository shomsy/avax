<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Capabilities\EnvironmentAwareness;

final readonly class TestingPolicy implements EnvironmentConfig
{
    public function errorDetail(): string
    {
        return 'full';
    }

    public function cacheEnabled(): bool
    {
        return false;
    }

    public function queueSync(): bool
    {
        return true;
    }

    public function securityHeadersStrict(): bool
    {
        return false;
    }

    public function debugEndpointsEnabled(): bool
    {
        return true;
    }

    public function queryLogEnabled(): bool
    {
        return true;
    }
}
