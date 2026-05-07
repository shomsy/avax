<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Capabilities\Distribution\UseCacheTiers;

use Avax\Components\Application\Cache\System\Capabilities\Distribution\UseCacheTiers\CacheTier;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\UseCacheTiers\CacheTierName;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\UseCacheTiers\TieredCache;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use Override;
use PHPUnit\Framework\TestCase;

final class TieredCacheTest extends TestCase
{
    private FrozenClock $frozenClock;

    public function test_read_promotes_to_faster_tier_on_hit() : void
    {
        $tieredCache = $this->createTieredCache();

        $tieredCache->getTier(cacheTierName: CacheTierName::L1_MEMORY)->write(
            cacheKey         : $this->makeKey(key: 'key_1'),
            storedCacheRecord: $this->makeRecord(value: 'value_1'),
        );

        $tieredCache->read(clock: $this->frozenClock, cacheKey: $this->makeKey(key: 'key_1'));

        $this->assertTrue(condition: $tieredCache->getTier(cacheTierName: CacheTierName::L1_MEMORY)->exists(cacheKey: $this->makeKey(key: 'key_1')));
    }

    private function createTieredCache() : TieredCache
    {
        $cacheTier = CacheTier::l1(name: 'l1_memory', maxSize: 100);
        $l2Tier    = CacheTier::l2(name: 'l2_distributed', maxSize: 1000);

        $l1Store = new InMemoryCacheStore(clock: $this->frozenClock, maxEntries: 100);
        $l2Store = new InMemoryCacheStore(clock: $this->frozenClock, maxEntries: 1000);

        $tieredCache = new TieredCache($this->frozenClock, $cacheTier, $l2Tier);
        $tieredCache->registerTier(cacheTier: $cacheTier, cacheStore: $l1Store);
        $tieredCache->registerTier(cacheTier: $l2Tier, cacheStore: $l2Store);

        return $tieredCache;
    }

    private function makeKey(string $key) : CacheKey
    {
        return CacheKey::create(key: $key);
    }

    private function makeRecord(string $value, int $ttlSeconds = 3600) : StoredCacheRecord
    {
        $now                  = $this->frozenClock->now();
        $cachedValueLifecycle = CachedValueLifecycle::create(
            createdAt: $now,
            expiresAt: $now->add(duration: Duration::ofSeconds(seconds: $ttlSeconds)),
            clock    : $this->frozenClock,
        );

        return new StoredCacheRecord(value: $value, cachedValueLifecycle: $cachedValueLifecycle);
    }

    public function test_read_falls_through_to_l2_on_l1_miss() : void
    {
        $tieredCache = $this->createTieredCache();

        $tieredCache->getTier(cacheTierName: CacheTierName::L2_DISTRIBUTED)->write(
            cacheKey         : $this->makeKey(key: 'key_2'),
            storedCacheRecord: $this->makeRecord(value: 'value_2'),
        );

        $result = $tieredCache->read(clock: $this->frozenClock, cacheKey: $this->makeKey(key: 'key_2'));

        $this->assertInstanceOf(expected: CacheStoreRecordWasFound::class, actual: $result);
    }

    public function test_read_returns_missing_when_not_found_in_any_tier() : void
    {
        $tieredCache = $this->createTieredCache();

        $result = $tieredCache->read(clock: $this->frozenClock, cacheKey: $this->makeKey(key: 'missing'));

        $this->assertInstanceOf(expected: CacheStoreRecordWasMissing::class, actual: $result);
    }

    public function test_write_stores_in_all_tiers() : void
    {
        $tieredCache = $this->createTieredCache();

        $tieredCache->write(cacheKey: $this->makeKey(key: 'key_1'), storedCacheRecord: $this->makeRecord(value: 'value_1'));

        $this->assertTrue(condition: $tieredCache->getTier(cacheTierName: CacheTierName::L1_MEMORY)->exists(cacheKey: $this->makeKey(key: 'key_1')));
        $this->assertTrue(condition: $tieredCache->getTier(cacheTierName: CacheTierName::L2_DISTRIBUTED)->exists(cacheKey: $this->makeKey(key: 'key_1')));
    }

    public function test_forget_removes_from_all_tiers() : void
    {
        $tieredCache = $this->createTieredCache();

        $tieredCache->write(cacheKey: $this->makeKey(key: 'key_1'), storedCacheRecord: $this->makeRecord(value: 'value_1'));
        $tieredCache->forget(cacheKey: $this->makeKey(key: 'key_1'));

        $this->assertFalse(condition: $tieredCache->getTier(cacheTierName: CacheTierName::L1_MEMORY)->exists(cacheKey: $this->makeKey(key: 'key_1')));
        $this->assertFalse(condition: $tieredCache->getTier(cacheTierName: CacheTierName::L2_DISTRIBUTED)->exists(cacheKey: $this->makeKey(key: 'key_1')));
    }

    public function test_clear_removes_from_all_tiers() : void
    {
        $tieredCache = $this->createTieredCache();

        $tieredCache->write(cacheKey: $this->makeKey(key: 'key_1'), storedCacheRecord: $this->makeRecord(value: 'value_1'));
        $tieredCache->write(cacheKey: $this->makeKey(key: 'key_2'), storedCacheRecord: $this->makeRecord(value: 'value_2'));

        $tieredCache->clear();

        $this->assertFalse(condition: $tieredCache->getTier(cacheTierName: CacheTierName::L1_MEMORY)->exists(cacheKey: $this->makeKey(key: 'key_1')));
        $this->assertFalse(condition: $tieredCache->getTier(cacheTierName: CacheTierName::L2_DISTRIBUTED)->exists(cacheKey: $this->makeKey(key: 'key_1')));
    }

    public function test_exists_returns_true_if_found_in_any_tier() : void
    {
        $tieredCache = $this->createTieredCache();

        $tieredCache->getTier(cacheTierName: CacheTierName::L2_DISTRIBUTED)->write(
            cacheKey         : $this->makeKey(key: 'key_1'),
            storedCacheRecord: $this->makeRecord(value: 'value_1'),
        );

        $this->assertTrue(condition: $tieredCache->exists(cacheKey: $this->makeKey(key: 'key_1')));
    }

    public function test_promotion_only_to_faster_tiers() : void
    {
        $tieredCache = $this->createTieredCache();

        $tieredCache->getTier(cacheTierName: CacheTierName::L2_DISTRIBUTED)->write(
            cacheKey         : $this->makeKey(key: 'key_1'),
            storedCacheRecord: $this->makeRecord(value: 'value_1'),
        );

        $tieredCache->read(clock: $this->frozenClock, cacheKey: $this->makeKey(key: 'key_1'));

        $this->assertTrue(condition: $tieredCache->getTier(cacheTierName: CacheTierName::L1_MEMORY)->exists(cacheKey: $this->makeKey(key: 'key_1')));
        $this->assertTrue(condition: $tieredCache->getTier(cacheTierName: CacheTierName::L2_DISTRIBUTED)->exists(cacheKey: $this->makeKey(key: 'key_1')));
    }

    public function test_get_tier_returns_correct_store() : void
    {
        $tieredCache = $this->createTieredCache();

        $l1 = $tieredCache->getTier(cacheTierName: CacheTierName::L1_MEMORY);
        $l2 = $tieredCache->getTier(cacheTierName: CacheTierName::L2_DISTRIBUTED);

        $this->assertNotNull(actual: $l1);
        $this->assertNotNull(actual: $l2);
        $this->assertInstanceOf(expected: InMemoryCacheStore::class, actual: $l1);
        $this->assertInstanceOf(expected: InMemoryCacheStore::class, actual: $l2);
    }

    #[Override]
    protected function setUp() : void
    {
        $this->frozenClock = new FrozenClock(timestamp: Timestamp::now());
    }
}
