<?php

declare(strict_types=1);

namespace Avax\Cache\Examples;

use Avax\Cache\Cache;
use Avax\Cache\System\AvaxCache;
use Avax\Cache\System\Capabilities\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Foundation\Time\FrozenClock;

require __DIR__ . '/../../vendor/autoload.php';

$clock = new FrozenClock(Timestamp::now());

$store = new InMemoryCacheStore(
    clock: $clock
);

$cache = new AvaxCache(
    store: $store,
    clock: $clock
);

Cache::use($cache);

$cache->set('user:42', [
    'id'    => 42,
    'name'  => 'John Doe',
    'email' => 'john@example.com',
], ttl:     3600);

$result = Cache::get('user:42');

print_r($result);

$result = Cache::remember('user:99', 3600, fn () => [
    'id'    => 99,
    'name'  => 'Jane Doe',
    'email' => 'jane@example.com',
]);

print_r($result);