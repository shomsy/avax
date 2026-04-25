<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Contract\Cache;

use Avax\Cache\System\Capabilities\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Foundation\Time\Clock;

final class InMemoryCacheStoreContractTest extends CacheStoreContractTest
{
    protected function createStore(Clock $clock) : InMemoryCacheStore
    {
        return new InMemoryCacheStore($clock);
    }
}