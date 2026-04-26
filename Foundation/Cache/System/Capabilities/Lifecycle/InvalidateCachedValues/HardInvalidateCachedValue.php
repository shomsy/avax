<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

interface HardInvalidateCachedValue
{
    public function delete(CacheKey $key) : void;

    public function exists(CacheKey $key) : bool;
}