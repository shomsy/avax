<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Persistence;

use Avax\Components\DataStack\Database\System\Capabilities\Observability\QueryFingerprinter;
use Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\CacheEntry;
use Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\CacheResult;
use Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\ReadCache;
use PHPUnit\Framework\TestCase;

final class ReadCacheTest extends TestCase
{
    // ==================== remember() pattern ====================

    public function test_remember_returns_cached_value_on_hit() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1');

        $callCount = 0;
        $result    = $cache->remember('key1', static function () use (&$callCount) {
            $callCount++;

            return 'computed';
        });

        $this->assertSame('value1', $result);
        $this->assertSame(0, $callCount);
    }

    public function test_remember_computes_value_on_miss() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);

        $result = $cache->remember('key1', static function () {
            return 'computed_value';
        });

        $this->assertSame('computed_value', $result);
    }

    public function test_remember_caches_computed_value() : void
    {
        $cache     = new ReadCache(defaultTtl: 60.0);
        $callCount = 0;

        $first  = $cache->remember('key1', static function () use (&$callCount) {
            $callCount++;

            return 'computed';
        });
        $second = $cache->remember('key1', static function () use (&$callCount) {
            $callCount++;

            return 'should_not_be_called';
        });

        $this->assertSame('computed', $first);
        $this->assertSame('computed', $second);
        $this->assertSame(1, $callCount);
    }

    public function test_remember_with_custom_ttl() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);

        $result = $cache->remember('key1', static function () {
            return 'ttl_test';
        }, ttl:                    120.0);

        $this->assertSame('ttl_test', $result);
    }

    public function test_remember_with_tags() : void
    {
        $cache     = new ReadCache(defaultTtl: 60.0);
        $callCount = 0;

        $cache->remember('key1', static function () use (&$callCount) {
            $callCount++;

            return 'tagged';
        }, tags:         ['users', 'active']);

        // Now invalidate the tag
        $cache->invalidateTag('users');

        // Should recompute
        $result = $cache->remember('key1', static function () use (&$callCount) {
            $callCount++;

            return 'recomputed';
        });

        $this->assertSame('recomputed', $result);
        $this->assertSame(2, $callCount);
    }

    // ==================== TTL expiration ====================

    public function test_get_hit_for_valid_entry() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1');

        $result = $cache->get('key1');

        $this->assertTrue($result->hit);
        $this->assertSame('value1', $result->value);
    }

    public function test_get_miss_for_nonexistent_key() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);

        $result = $cache->get('nonexistent');

        $this->assertFalse($result->hit);
    }

    public function test_expired_entry_returns_miss() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1', ttl: 0.001); // 1ms TTL

        usleep(2000); // Wait 2ms

        $result = $cache->get('key1');

        $this->assertFalse($result->hit);
    }

    public function test_has_returns_false_for_expired_key() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1', ttl: 0.001);

        usleep(2000);

        $this->assertFalse($cache->has('key1'));
    }

    public function test_has_returns_true_for_valid_key() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1');

        $this->assertTrue($cache->has('key1'));
    }

    // ==================== Tag-based invalidation ====================

    public function test_invalidate_tag_removes_entries() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1', tags: ['users']);
        $cache->put('key2', 'value2', tags: ['users']);
        $cache->put('key3', 'value3', tags: ['orders']);

        $count = $cache->invalidateTag('users');

        $this->assertSame(2, $count);
        $this->assertFalse($cache->get('key1')->hit);
        $this->assertFalse($cache->get('key2')->hit);
        $this->assertTrue($cache->get('key3')->hit);
    }

    public function test_invalidate_nonexistent_tag() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);

        $count = $cache->invalidateTag('nonexistent');

        $this->assertSame(0, $count);
    }

    public function test_entry_with_multiple_tags() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1', tags: ['users', 'admin']);

        $cache->invalidateTag('users');

        $this->assertFalse($cache->get('key1')->hit);
    }

    // ==================== Fingerprint-based invalidation ====================

    public function test_invalidate_fingerprint_removes_entries() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1', fingerprint: 'fp1');
        $cache->put('key2', 'value2', fingerprint: 'fp1');
        $cache->put('key3', 'value3', fingerprint: 'fp2');

        $count = $cache->invalidateFingerprint('fp1');

        $this->assertSame(2, $count);
        $this->assertFalse($cache->get('key1')->hit);
        $this->assertFalse($cache->get('key2')->hit);
        $this->assertTrue($cache->get('key3')->hit);
    }

    public function test_invalidate_nonexistent_fingerprint() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);

        $count = $cache->invalidateFingerprint('nonexistent');

        $this->assertSame(0, $count);
    }

    // ==================== Table-based invalidation ====================

    public function test_invalidate_tables_uses_tag_prefix() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1', tags: ['table:users']);
        $cache->put('key2', 'value2', tags: ['table:users']);
        $cache->put('key3', 'value3', tags: ['table:orders']);

        $count = $cache->invalidateTables(['users']);

        $this->assertSame(2, $count);
        $this->assertFalse($cache->get('key1')->hit);
        $this->assertFalse($cache->get('key2')->hit);
        $this->assertTrue($cache->get('key3')->hit);
    }

    public function test_invalidate_multiple_tables() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1', tags: ['table:users']);
        $cache->put('key2', 'value2', tags: ['table:orders']);
        $cache->put('key3', 'value3', tags: ['table:products']);

        $count = $cache->invalidateTables(['users', 'orders']);

        $this->assertSame(2, $count);
        $this->assertTrue($cache->get('key3')->hit);
    }

    // ==================== Hit rate tracking ====================

    public function test_hit_rate_initially_zero() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);

        $this->assertSame(0.0, $cache->hitRate());
    }

    public function test_hit_rate_after_hits_and_misses() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1');

        $cache->get('key1');  // hit
        $cache->get('key1');  // hit
        $cache->get('key2');  // miss
        $cache->get('key3');  // miss

        $this->assertSame(0.5, $cache->hitRate());
    }

    public function test_hit_rate_all_hits() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1');

        $cache->get('key1');
        $cache->get('key1');
        $cache->get('key1');

        $this->assertSame(1.0, $cache->hitRate());
    }

    public function test_hit_rate_all_misses() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);

        $cache->get('key1');
        $cache->get('key2');
        $cache->get('key3');

        $this->assertSame(0.0, $cache->hitRate());
    }

    // ==================== Statistics ====================

    public function test_stats_returns_all_fields() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1', tags: ['users'], fingerprint: 'fp1');
        $cache->get('key1');
        $cache->get('key2');

        $stats = $cache->stats();

        $this->assertArrayHasKey('hits', $stats);
        $this->assertArrayHasKey('misses', $stats);
        $this->assertArrayHasKey('hit_rate', $stats);
        $this->assertArrayHasKey('total_entries', $stats);
        $this->assertArrayHasKey('active_entries', $stats);
        $this->assertArrayHasKey('tags', $stats);
        $this->assertArrayHasKey('fingerprints', $stats);
    }

    public function test_stats_values() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1', tags: ['users']);
        $cache->put('key2', 'value2', tags: ['orders']);
        $cache->get('key1'); // hit
        $cache->get('key3'); // miss

        $stats = $cache->stats();

        $this->assertSame(1, $stats['hits']);
        $this->assertSame(1, $stats['misses']);
        $this->assertSame(0.5, $stats['hit_rate']);
        $this->assertSame(2, $stats['total_entries']);
        $this->assertSame(2, $stats['active_entries']);
    }

    // ==================== Cache entry properties ====================

    public function test_cache_entry_is_expired() : void
    {
        $entry = new CacheEntry(value: 'test', createdAt: 0.0, ttl: 1.0);

        $this->assertTrue($entry->isExpired(now: 2.0));
        $this->assertFalse($entry->isExpired(now: 0.5));
    }

    public function test_cache_entry_remaining_ttl() : void
    {
        $entry = new CacheEntry(value: 'test', createdAt: 0.0, ttl: 10.0);

        $this->assertSame(7.0, $entry->remainingTtl(now: 3.0));
    }

    public function test_cache_entry_remaining_ttl_expired() : void
    {
        $entry = new CacheEntry(value: 'test', createdAt: 0.0, ttl: 5.0);

        $this->assertSame(0.0, $entry->remainingTtl(now: 10.0));
    }

    public function test_cache_entry_age() : void
    {
        $entry = new CacheEntry(value: 'test', createdAt: 0.0, ttl: 10.0);

        $this->assertSame(3.0, $entry->age(now: 3.0));
    }

    public function test_cache_entry_fingerprint() : void
    {
        $entry = new CacheEntry(value: 'test', createdAt: 0.0, ttl: 10.0, fingerprint: 'fp123');

        $this->assertSame('fp123', $entry->fingerprint);
    }

    public function test_cache_entry_key() : void
    {
        $entry = new CacheEntry(value: 'test', createdAt: 0.0, ttl: 10.0, key: 'my_key');

        $this->assertSame('my_key', $entry->key);
    }

    // ==================== CacheResult ====================

    public function test_cache_result_hit_factory() : void
    {
        $result = CacheResult::hit(value: 'test_value', key: 'key1', ttl: 30.0);

        $this->assertTrue($result->hit);
        $this->assertSame('test_value', $result->value);
        $this->assertSame('key1', $result->key);
        $this->assertSame(30.0, $result->ttl);
    }

    public function test_cache_result_miss_factory() : void
    {
        $result = CacheResult::miss(key: 'key1');

        $this->assertFalse($result->hit);
        $this->assertNull($result->value);
        $this->assertSame('key1', $result->key);
    }

    // ==================== forget() ====================

    public function test_forget_removes_entry() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1');

        $cache->forget('key1');

        $this->assertFalse($cache->get('key1')->hit);
    }

    public function test_forget_nonexistent_key() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);

        $cache->forget('nonexistent');

        $this->assertFalse($cache->get('nonexistent')->hit);
    }

    // ==================== prune() ====================

    public function test_prune_removes_expired_entries() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1', ttl: 0.001);
        $cache->put('key2', 'value2', ttl: 60.0);

        usleep(2000);

        $pruned = $cache->prune();

        $this->assertSame(1, $pruned);
        $this->assertFalse($cache->get('key1')->hit);
        $this->assertTrue($cache->get('key2')->hit);
    }

    public function test_prune_no_expired_entries() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1');
        $cache->put('key2', 'value2');

        $pruned = $cache->prune();

        $this->assertSame(0, $pruned);
    }

    // ==================== count() ====================

    public function test_count_returns_active_entries() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1');
        $cache->put('key2', 'value2');
        $cache->put('key3', 'value3', ttl: 0.001);

        usleep(2000);

        $this->assertSame(2, $cache->count());
    }

    // ==================== flush() ====================

    public function test_flush_clears_everything() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1', tags: ['users']);
        $cache->get('key1');

        $cache->flush();

        $this->assertFalse($cache->get('key1')->hit);
        $this->assertSame(0, $cache->count());
        $this->assertSame(0.0, $cache->hitRate());
    }

    // ==================== cacheQuery / getQuery ====================

    public function test_cache_query() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $sql   = 'SELECT * FROM users WHERE id = 1';

        $cache->cacheQuery($sql, ['id' => 1, 'name' => 'John']);

        $result = $cache->getQuery($sql);

        $this->assertTrue($result->hit);
        $this->assertSame(['id' => 1, 'name' => 'John'], $result->value);
    }

    public function test_get_query_miss() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);

        $result = $cache->getQuery('SELECT * FROM nonexistent');

        $this->assertFalse($result->hit);
    }

    public function test_cache_query_with_tags() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $sql   = 'SELECT * FROM users';

        $cache->cacheQuery($sql, ['users'], tags: ['table:users']);

        $cache->invalidateTables(['users']);

        $this->assertFalse($cache->getQuery($sql)->hit);
    }

    // ==================== put() with various configurations ====================

    public function test_put_with_custom_ttl() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1', ttl: 0.001);

        usleep(2000);

        $this->assertFalse($cache->get('key1')->hit);
    }

    public function test_put_uses_default_ttl() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0);
        $cache->put('key1', 'value1');

        $this->assertTrue($cache->get('key1')->hit);
    }

    // ==================== Max entries eviction ====================

    public function test_max_entries_evicts_oldest() : void
    {
        $cache = new ReadCache(defaultTtl: 60.0, maxEntries: 2);
        $cache->put('key1', 'value1');
        usleep(1000);
        $cache->put('key2', 'value2');
        usleep(1000);
        $cache->put('key3', 'value3');

        $this->assertSame(2, $cache->count());
        $this->assertFalse($cache->get('key1')->hit);
        $this->assertTrue($cache->get('key2')->hit);
        $this->assertTrue($cache->get('key3')->hit);
    }
}
