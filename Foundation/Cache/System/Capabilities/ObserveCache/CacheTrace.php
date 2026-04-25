<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ObserveCache;

interface CacheTrace
{
    public function trace(CacheOperation $operation) : void;

    public function getOperations(?string $key = null) : array;

    public function clear() : void;
}