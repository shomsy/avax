<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

enum CacheNodeStatus: string
{
    case HEALTHY   = 'healthy';
    case UNHEALTHY = 'unhealthy';
    case DRAINING  = 'draining';
    case MAINTENANCE = 'maintenance';

    public function isAvailable() : bool
    {
        return match ($this) {
            self::HEALTHY => true,
            self::UNHEALTHY, self::DRAINING, self::MAINTENANCE => false,
        };
    }
}
