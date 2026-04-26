<?php

declare(strict_types=1);

namespace Avax\Cache\Examples;

use Avax\Cache\Cache;
use Avax\Cache\System\Capabilities\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Foundation\Time\SystemClock;

require __DIR__ . '/../../vendor/autoload.php';

$l1 = new InMemoryCacheStore();
$clock = new SystemClock();

$cache = Cache::use($l1);

for ($i = 0; $i < 10; $i++) {
    Cache::remember("item:{$i}", 3600, fn () => ["id" => $i, "name" => "Item {$i}"]);
}

$result = Cache::get('item:5');
print_r($result);

Cache::clear();

$result = Cache::has('item:5');
echo "After clear, has item:5 = " . ($result ? 'true' : 'false') . "\n";