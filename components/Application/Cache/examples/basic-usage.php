<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\Examples;

use Avax\Components\Application\Cache\Cache;
use Avax\Components\Application\Cache\System\AvaxCache;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;

require __DIR__ . '/../../vendor/autoload.php';

$clock = new FrozenClock(timestamp: Timestamp::now());

$store = new InMemoryCacheStore(
    clock: $clock,
);

$cache = new AvaxCache(
    clock: $clock,
    store: $store,
);

Cache::use(cache: $cache);

$cache->set(value: [
    'id'    => 42,
    'name'  => 'John Doe',
    'email' => 'john@example.com',
],          ttl: 3600, cacheKey: 'user:42');

$result = Cache::get(cacheKey: 'user:42');

print_r($result);

$result = Cache::remember(ttl: 3600, loader: static fn () : array => [
    'id'    => 99,
    'name'  => 'Jane Doe',
    'email' => 'jane@example.com',
], cacheKey: 'user:99');

print_r($result);
