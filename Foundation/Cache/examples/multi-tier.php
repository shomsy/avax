<?php

declare(strict_types=1);

namespace Avax\Cache\Examples;

use Avax\Cache\Cache\AvaxCache;
use Avax\Cache\Cache\Capabilities\StoreCachedValues\FileCacheStore;
use Avax\Cache\Cache\Capabilities\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\Cache\Foundation\Time\SystemClock;

require __DIR__ . '/../../vendor/autoload.php';

$l1    = new InMemoryCacheStore(maxEntries: 100);
$l2    = new FileCacheStore(directory: '/tmp/cache');
$clock = new SystemClock();

$cache = new AvaxCache(
    store         : $l1,
    clock         : $clock,
    defaultTtl    : 3600,
    fallbackStores: [$l2]
);

for ($i = 0; $i < 10; $i++) {
    $cache->remember("item:{$i}", fn () => ["id" => $i, "name" => "Item {$i}"]);
}

$stats = $cache->inspect()->metrics();

echo "Hits: {$stats->hitCount}\n";
echo "Misses: {$stats->missCount}\n";
echo "Hit rate: " . ($stats->hitCount / ($stats->hitCount + $stats->missCount) * 100) . "%\n";