<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\Tests\Contract\Cache;

require_once __DIR__ . '/CacheStoreContractCase.php';

use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Override;

final class InMemoryCacheStoreContractTest extends CacheStoreContractCase
{
    #[Override]
    protected function createStore(Clock $clock) : InMemoryCacheStore
    {
        return new InMemoryCacheStore(clock: $clock);
    }
}
