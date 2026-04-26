<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class CacheStoreRecordWasMissing
{
    public function __construct(
        public CacheKey $key
    ) {}
}