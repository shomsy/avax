<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;

/**
 * Value object representing the result of a promotion operation.
 */
final readonly class PromotionResult
{
    public function __construct(
        public CacheStore $oldPrimary,
        public CacheStore $newPrimary,
        public int $promotedReplicaIndex,
        public bool $success,
    ) {
    }
}
