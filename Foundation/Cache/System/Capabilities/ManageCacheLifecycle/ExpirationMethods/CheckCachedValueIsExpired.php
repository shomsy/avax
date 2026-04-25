<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\ExpirationMethods;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Timestamp;

final readonly class CheckCachedValueIsExpired
{
    public function __construct(
        private Clock $clock
    ) {}

    public function check(?Timestamp $expiresAt) : bool
    {
        if ($expiresAt === null) {
            return false;
        }

        return $this->clock->now()->isAfter($expiresAt);
    }

    public function secondsUntilExpiry(?Timestamp $expiresAt) : int
    {
        if ($expiresAt === null) {
            return PHP_INT_MAX;
        }

        $diff = $expiresAt->difference($this->clock->now());

        return max(0, $diff->toSeconds());
    }
}