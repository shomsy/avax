<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues;

enum CachedValueState: string
{
    case ACTIVE      = 'active';
    case EXPIRING_SOON = 'expiring_soon';
    case EXPIRED     = 'expired';
    case STALE       = 'stale';
    case INVALIDATED = 'invalidated';
    case EVICTED     = 'evicted';
    case MISSING     = 'missing';
    case STORE_FAILURE = 'store_failure';

    public function isUsable() : bool
    {
        return match ($this) {
            self::ACTIVE, self::EXPIRING_SOON, self::STALE => true,
            default => false,
        };
    }

    public function indicatesHit() : bool
    {
        return match ($this) {
            self::ACTIVE, self::EXPIRING_SOON, self::STALE => true,
            default => false,
        };
    }

    public function indicatesMiss() : bool
    {
        return match ($this) {
            self::MISSING, self::EXPIRED, self::INVALIDATED, self::EVICTED => true,
            default => false,
        };
    }
}
