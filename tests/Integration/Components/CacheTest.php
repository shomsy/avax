<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\Components;

use Avax\Components\Performance\System\PublicSurface\Performance;
use Avax\Tests\TestCase;

final class CacheTest extends TestCase
{
    public function test_query_cache_returns_cached_value_across_calls(): void
    {
        $calls = 0;
        $cache = Performance::queryCache();

        $cache->remember(key: 'integration-cache', query: static function () use (&$calls): string {
            $calls++;

            return 'cached';
        });
        $value = $cache->remember(key: 'integration-cache', query: static function () use (&$calls): string {
            $calls++;

            return 'fresh';
        });

        self::assertSame(expected: 'cached', actual: $value);
        self::assertSame(expected: 1, actual: $calls);
    }
}
