<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\StoreCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;

final readonly class CacheStoreRecordWasMissing
{
    public function __construct(
        public CacheKey $key
    ) {}
}