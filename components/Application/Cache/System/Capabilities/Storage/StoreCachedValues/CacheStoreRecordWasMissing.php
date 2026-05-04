<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class CacheStoreRecordWasMissing
{
    public function __construct(public CacheKey $cacheKey) {
    }
}
