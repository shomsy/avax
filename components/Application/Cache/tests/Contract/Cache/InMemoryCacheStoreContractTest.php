<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\Tests\Contract\Cache;

use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Override;

final class InMemoryCacheStoreContractTest extends CacheStoreContractTest
{
    #[Override]
    protected function createStore(Clock $clock) : InMemoryCacheStore
    {
        return new InMemoryCacheStore(clock: $clock);
    }
}
