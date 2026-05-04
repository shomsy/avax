<?php

declare(strict_types=1);

namespace Avax\Components\Config\System\Capabilities\EnvironmentAwareness;

use Avax\Components\Config\System\Capabilities\EnvironmentAwareness\Detection\EnvironmentDetector;
use Avax\Components\Config\System\Capabilities\EnvironmentAwareness\System\Capabilities\Policies\LocalPolicy;
use Avax\Components\Config\System\Capabilities\EnvironmentAwareness\System\Capabilities\Policies\ProductionPolicy;
use Avax\Components\Config\System\Capabilities\EnvironmentAwareness\System\Capabilities\Policies\StagingPolicy;
use Avax\Components\Config\System\Capabilities\EnvironmentAwareness\System\Capabilities\Policies\TestingPolicy;

interface EnvironmentConfig
{
    public function errorDetail(): string;

    public function cacheEnabled(): bool;

    public function queueSync(): bool;

    public function securityHeadersStrict(): bool;

    public function debugEndpointsEnabled(): bool;

    public function queryLogEnabled(): bool;
}
