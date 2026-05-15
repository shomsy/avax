<?php

declare(strict_types=1);

namespace Avax\Examples\Cache;

use Avax\Components\Application\Cache\System\AvaxCache;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastRecentlyUsedReplacement;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\PublicSurface\Cache;

require __DIR__.'/../../vendor/autoload.php';

$clock = new SystemClock();
$l1 = new InMemoryCacheStore(
    clock                          : $clock,
    chooseCachedValueForReplacement: new LeastRecentlyUsedReplacement(),
);

$cache = new AvaxCache(clock: $clock, store: $l1);

Cache::use(cache: $cache);

for ($i = 0; $i < 10; $i++) {
    Cache::remember(ttl: 3600, loader: static fn (): array => ['id' => $i, 'name' => 'Item '.$i], cacheKey: 'item:'.$i);
}

$result = Cache::get(cacheKey: 'item:5');
print_r($result);

Cache::clear();

$result = Cache::has(cacheKey: 'item:5');
echo 'After clear, has item:5 = '.($result ? 'true' : 'false')."\n";
