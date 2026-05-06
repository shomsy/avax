<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Capabilities\EnvironmentAwareness;

interface EnvironmentConfig
{
    public function errorDetail(): string;

    public function cacheEnabled(): bool;

    public function queueSync(): bool;

    public function securityHeadersStrict(): bool;

    public function debugEndpointsEnabled(): bool;

    public function queryLogEnabled(): bool;
}
