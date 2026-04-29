<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Contract\Cache;

use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;

final class InMemoryCacheStoreContractTest extends CacheStoreContractTest
{
    protected function createStore(Clock $clock) : InMemoryCacheStore
    {
        return new InMemoryCacheStore(clock: $clock);
    }
}