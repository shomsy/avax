<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\tests\Unit\Cache\Capabilities\Source\ProtectCacheSource;

use Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource\InMemoryLockStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use Override;
use PHPUnit\Framework\TestCase;

final class InMemoryLockStoreTest extends TestCase
{
    private FrozenClock $frozenClock;

    public function test_acquire_returns_true_when_no_lock_exists(): void
    {
        $inMemoryLockStore = new InMemoryLockStore();
        $result            = $inMemoryLockStore->acquire(key: 'test_key', ttlSeconds: 30);
        $this->assertTrue(condition: $result);
    }

    public function test_acquire_returns_false_when_lock_is_held(): void
    {
        $inMemoryLockStore = new InMemoryLockStore();
        $inMemoryLockStore->acquire(key: 'test_key', ttlSeconds: 30);

        $result = $inMemoryLockStore->acquire(key: 'test_key', ttlSeconds: 30);
        $this->assertFalse(condition: $result);
    }

    public function test_acquire_returns_true_after_lock_expires(): void
    {
        $inMemoryLockStore = new InMemoryLockStore(clock: $this->frozenClock);
        $inMemoryLockStore->acquire(key: 'test_key', ttlSeconds: 1);

        $this->assertTrue(condition: $inMemoryLockStore->isAcquired(key: 'test_key'));

        $this->frozenClock->moveForward(duration: Duration::ofSeconds(seconds: 2));

        $this->assertFalse(condition: $inMemoryLockStore->isAcquired(key: 'test_key'));

        $result = $inMemoryLockStore->acquire(key: 'test_key', ttlSeconds: 30);
        $this->assertTrue(condition: $result);
    }

    public function test_release_removes_lock(): void
    {
        $inMemoryLockStore = new InMemoryLockStore();
        $inMemoryLockStore->acquire(key: 'test_key', ttlSeconds: 30);

        $this->assertTrue(condition: $inMemoryLockStore->isAcquired(key: 'test_key'));

        $inMemoryLockStore->release(key: 'test_key');

        $this->assertFalse(condition: $inMemoryLockStore->isAcquired(key: 'test_key'));
    }

    public function test_acquire_after_release_succeeds(): void
    {
        $inMemoryLockStore = new InMemoryLockStore();
        $inMemoryLockStore->acquire(key: 'test_key', ttlSeconds: 30);
        $inMemoryLockStore->release(key: 'test_key');

        $result = $inMemoryLockStore->acquire(key: 'test_key', ttlSeconds: 30);
        $this->assertTrue(condition: $result);
    }

    public function test_get_owner_returns_lock_owner(): void
    {
        $inMemoryLockStore = new InMemoryLockStore();
        $inMemoryLockStore->acquire(key: 'test_key', ttlSeconds: 30);

        $owner = $inMemoryLockStore->getOwner(key: 'test_key');
        $this->assertNotNull(actual: $owner);
        $this->assertNotEmpty(actual: $owner->ownerId);
    }

    public function test_get_owner_returns_null_when_not_locked(): void
    {
        $inMemoryLockStore = new InMemoryLockStore();
        $owner             = $inMemoryLockStore->getOwner(key: 'test_key');
        $this->assertNull(actual: $owner);
    }

    public function test_release_all_clears_all_locks(): void
    {
        $inMemoryLockStore = new InMemoryLockStore();
        $inMemoryLockStore->acquire(key: 'key1', ttlSeconds: 30);
        $inMemoryLockStore->acquire(key: 'key2', ttlSeconds: 30);

        $this->assertTrue(condition: $inMemoryLockStore->isAcquired(key: 'key1'));
        $this->assertTrue(condition: $inMemoryLockStore->isAcquired(key: 'key2'));

        $inMemoryLockStore->releaseAll();

        $this->assertFalse(condition: $inMemoryLockStore->isAcquired(key: 'key1'));
        $this->assertFalse(condition: $inMemoryLockStore->isAcquired(key: 'key2'));
    }

    #[Override]
    protected function setUp(): void
    {
        $this->frozenClock = new FrozenClock(timestamp: Timestamp::now());
    }
}
