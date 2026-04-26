<?php

declare(strict_types=1);

namespace Avax\Cache\Examples;

use Avax\Cache\Cache;
use Avax\Cache\System\Capabilities\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Foundation\Time\SystemClock;

require __DIR__ . '/../../vendor/autoload.php';

$l1 = new InMemoryCacheStore();
$clock = new SystemClock();

$cache = Cache::use(cache: $l1);

for ($i = 0; $i < 10; $i++) {
    Cache::remember(key: "item:{$i}", ttl: 3600, loader: fn () => ["id" => $i, "name" => "Item {$i}"]);
}

$result = Cache::get(key: 'item:5');
print_r($result);

Cache::clear();

$result = Cache::has(key: 'item:5');
echo "After clear, has item:5 = " . ($result ? 'true' : 'false') . "\n";