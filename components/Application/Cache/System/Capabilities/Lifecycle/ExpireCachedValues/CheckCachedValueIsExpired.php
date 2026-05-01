<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;

final readonly class CheckCachedValueIsExpired
{
    public function __construct(
        private Clock $clock,
    ) {
    }

    public function check(?Timestamp $timestamp): bool
    {
        if (! $timestamp instanceof Timestamp) {
            return false;
        }

        return $this->clock->now()->isAfter(other: $timestamp);
    }

    public function secondsUntilExpiry(?Timestamp $timestamp): int
    {
        if (! $timestamp instanceof Timestamp) {
            return PHP_INT_MAX;
        }

        $duration = $timestamp->difference(other: $this->clock->now());

        return max(0, $duration->toSeconds());
    }
}
