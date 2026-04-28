<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

interface HardInvalidateCachedValue
{
    public function delete(CacheKey $key) : void;

    public function exists(CacheKey $key) : bool;
}