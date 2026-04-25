<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\InvalidationMethods;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;

interface HardInvalidateCachedValue
{
    public function delete(CacheKey $key) : void;

    public function exists(CacheKey $key) : bool;
}