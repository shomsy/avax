<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Capabilities\EnvironmentAwareness;

use Avax\Components\Application\Config\System\Capabilities\EnvironmentAwareness\Detection\EnvironmentDetector;
use Avax\Components\Application\Config\System\Capabilities\EnvironmentAwareness\LocalPolicy;
use Avax\Components\Application\Config\System\Capabilities\EnvironmentAwareness\ProductionPolicy;
use Avax\Components\Application\Config\System\Capabilities\EnvironmentAwareness\StagingPolicy;
use Avax\Components\Application\Config\System\Capabilities\EnvironmentAwareness\TestingPolicy;

interface EnvironmentConfig
{
    public function errorDetail(): string;

    public function cacheEnabled(): bool;

    public function queueSync(): bool;

    public function securityHeadersStrict(): bool;

    public function debugEndpointsEnabled(): bool;

    public function queryLogEnabled(): bool;
}
