<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

enum InvalidationReason: string
{
    case EXPLICIT          = 'explicit';
    case TTL_EXPIRED       = 'ttl_expired';
    case STALE_POLICY      = 'stale_policy';
    case VERSION_CHANGE    = 'version_change';
    case TAG_INVALIDATION  = 'tag_invalidation';
    case NAMESPACE_CLEAR   = 'namespace_clear';
    case PATTERN_MATCH     = 'pattern_match';
    case CAPACITY_PRESSURE = 'capacity_pressure';
    case SOURCE_UPDATED    = 'source_updated';
    case MAINTENANCE       = 'maintenance';

    public function isAutomatic() : bool
    {
        return match ($this) {
            self::TTL_EXPIRED, self::STALE_POLICY, self::CAPACITY_PRESSURE => true,
            default                                                        => false,
        };
    }

    public function requiresEviction() : bool
    {
        return match ($this) {
            self::EXPLICIT, self::TTL_EXPIRED, self::STALE_POLICY, self::CAPACITY_PRESSURE => true,
            default                                                                        => false,
        };
    }
}
