<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\Components;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Flows\Lifecycle\RememberCachedValue\RememberCachedValue;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Tests\TestCase;

final class CacheTest extends TestCase
{
    public function test_query_cache_returns_cached_value_across_calls(): void
    {
        $calls = 0;
        $rememberCachedValue = new RememberCachedValue(
            cacheStore: new InMemoryCacheStore(),
            clock: new SystemClock(),
        );
        $cacheKey = CacheKey::create(key: 'integration-cache');

        $rememberCachedValue->remember(cacheKey: $cacheKey, ttl: 3600, loader: static function () use (&$calls): string {
            $calls++;

            return 'cached';
        });
        $value = $rememberCachedValue->remember(cacheKey: $cacheKey, ttl: 3600, loader: static function () use (&$calls): string {
            $calls++;

            return 'fresh';
        });

        self::assertSame('cached', $value);
        self::assertSame(1, $calls);
    }
}
