<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use InvalidArgumentException;
use Throwable;



/**
 * Value object representing the result of a promotion operation.
 */
final readonly class PromotionResult
{
    public function __construct(
        public CacheStore $oldPrimary,
        public CacheStore $newPrimary,
        public int  $promotedReplicaIndex,
        public bool $success,
    ) {}
}