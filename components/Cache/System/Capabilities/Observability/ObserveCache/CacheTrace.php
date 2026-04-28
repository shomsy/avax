<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Observability\ObserveCache;

interface CacheTrace
{
    public function trace(CacheOperation $operation) : void;

    public function getOperations(string|null $key = null) : array;

    public function clear() : void;
}