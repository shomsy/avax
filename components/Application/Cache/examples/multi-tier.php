<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\Examples;

use Avax\Components\Application\Cache\Cache;
use Avax\Components\Application\Cache\System\AvaxCache;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;

require __DIR__ . '/../../vendor/autoload.php';

$clock = new SystemClock;
$l1    = new InMemoryCacheStore(clock: $clock);

$cache = new AvaxCache(store: $l1, clock: $clock);

Cache::use(cache: $cache);

for ($i = 0; $i < 10; $i++) {
    Cache::remember(key: 'item:' . $i, ttl: 3600, loader: static fn () : array => ['id' => $i, 'name' => 'Item ' . $i]);
}

$result = Cache::get(key: 'item:5');
print_r($result);

Cache::clear();

$result = Cache::has(key: 'item:5');
echo 'After clear, has item:5 = ' . ($result ? 'true' : 'false') . "\n";
