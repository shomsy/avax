<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Capabilities\EnvironmentAwareness;

final readonly class ProductionPolicy implements EnvironmentConfig
{
    public function errorDetail(): string
    {
        return 'none';
    }

    public function cacheEnabled(): bool
    {
        return true;
    }

    public function queueSync(): bool
    {
        return false;
    }

    public function securityHeadersStrict(): bool
    {
        return true;
    }

    public function debugEndpointsEnabled(): bool
    {
        return false;
    }

    public function queryLogEnabled(): bool
    {
        return false;
    }
}
