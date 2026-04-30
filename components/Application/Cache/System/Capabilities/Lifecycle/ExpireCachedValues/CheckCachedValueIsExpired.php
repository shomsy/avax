<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;

final readonly class CheckCachedValueIsExpired
{
    public function __construct(
        private Clock $clock,
    ) {}

    public function check(Timestamp|null $expiresAt) : bool
    {
        if (! $expiresAt instanceof Timestamp) {
            return false;
        }

        return $this->clock->now()->isAfter(other: $expiresAt);
    }

    public function secondsUntilExpiry(Timestamp|null $expiresAt) : int
    {
        if (! $expiresAt instanceof Timestamp) {
            return PHP_INT_MAX;
        }

        $duration = $expiresAt->difference(other: $this->clock->now());

        return max(0, $duration->toSeconds());
    }
}
