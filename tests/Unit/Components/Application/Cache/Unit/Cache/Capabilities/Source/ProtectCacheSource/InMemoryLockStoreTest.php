<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Unit\Cache\Capabilities\Source\ProtectCacheSource;

use Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource\InMemoryLockStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

final class InMemoryLockStoreTest extends TestCase
{
    private FrozenClock $clock;

    public function test_acquire_returns_true_when_no_lock_exists() : void
    {
        $store = new InMemoryLockStore;
        $result = $store->acquire(key: 'test_key', ttlSeconds: 30);
        $this->assertTrue(condition: $result);
    }

    public function test_acquire_returns_false_when_lock_is_held() : void
    {
        $store = new InMemoryLockStore;
        $store->acquire(key: 'test_key', ttlSeconds: 30);

        $result = $store->acquire(key: 'test_key', ttlSeconds: 30);
        $this->assertFalse(condition: $result);
    }

    public function test_acquire_returns_true_after_lock_expires() : void
    {
        $store = new InMemoryLockStore(clock: $this->clock);
        $store->acquire(key: 'test_key', ttlSeconds: 1);

        $this->assertTrue(condition: $store->isAcquired(key: 'test_key'));

        $this->clock->moveForward(duration: Duration::ofSeconds(seconds: 2));

        $this->assertFalse(condition: $store->isAcquired(key: 'test_key'));

        $result = $store->acquire(key: 'test_key', ttlSeconds: 30);
        $this->assertTrue(condition: $result);
    }

    public function test_release_removes_lock() : void
    {
        $store = new InMemoryLockStore;
        $store->acquire(key: 'test_key', ttlSeconds: 30);

        $this->assertTrue(condition: $store->isAcquired(key: 'test_key'));

        $store->release(key: 'test_key');

        $this->assertFalse(condition: $store->isAcquired(key: 'test_key'));
    }

    public function test_acquire_after_release_succeeds() : void
    {
        $store = new InMemoryLockStore;
        $store->acquire(key: 'test_key', ttlSeconds: 30);
        $store->release(key: 'test_key');

        $result = $store->acquire(key: 'test_key', ttlSeconds: 30);
        $this->assertTrue(condition: $result);
    }

    public function test_get_owner_returns_lock_owner() : void
    {
        $store = new InMemoryLockStore;
        $store->acquire(key: 'test_key', ttlSeconds: 30);

        $owner = $store->getOwner(key: 'test_key');
        $this->assertNotNull(actual: $owner);
        $this->assertNotEmpty(actual: $owner->ownerId);
    }

    public function test_get_owner_returns_null_when_not_locked() : void
    {
        $store = new InMemoryLockStore;
        $owner = $store->getOwner(key: 'test_key');
        $this->assertNull(actual: $owner);
    }

    public function test_release_all_clears_all_locks() : void
    {
        $store = new InMemoryLockStore;
        $store->acquire(key: 'key1', ttlSeconds: 30);
        $store->acquire(key: 'key2', ttlSeconds: 30);

        $this->assertTrue(condition: $store->isAcquired(key: 'key1'));
        $this->assertTrue(condition: $store->isAcquired(key: 'key2'));

        $store->releaseAll();

        $this->assertFalse(condition: $store->isAcquired(key: 'key1'));
        $this->assertFalse(condition: $store->isAcquired(key: 'key2'));
    }

    protected function setUp() : void
    {
        $this->clock = new FrozenClock(timestamp: Timestamp::now());
    }
}
