<?php

declare(strict_types=1);

namespace Avax\Cache\Examples;

use Avax\Cache\Cache\AvaxCache;
use Avax\Cache\Cache\Cache;
use Avax\Cache\Cache\Capabilities\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\Cache\Foundation\Time\FrozenClock;
use Avax\Cache\Cache\Foundation\Time\SystemClock;

require __DIR__ . '/../../vendor/autoload.php';

$clock = new FrozenClock(new SystemClock());

$store = new InMemoryCacheStore(
    maxEntries: 100,
    clock     : $clock
);

$cache = new AvaxCache(
    store     : $store,
    clock     : $clock,
    defaultTtl: 3600
);

$cache->remember('user:42', function () : array {
    return [
        'id'    => 42,
        'name'  => 'John Doe',
        'email' => 'john@example.com',
    ];
});

$result = $cache->get('user:42');

print_r($result);