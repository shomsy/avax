<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Capabilities\ManageCacheLifecycle;

use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueLifecycle;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueState;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CacheLifecyclePolicy;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\FrozenClock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

final class CacheLifecyclePolicyTest extends TestCase
{
    public function test_returns_missing_state_when_lifecycle_is_null() : void
    {
        $clock  = new FrozenClock(Timestamp::now());
        $policy = new CacheLifecyclePolicy();

        $state = $policy->decide(lifecycle: null, clock: $clock);

        $this->assertSame(CachedValueState::MISSING, $state);
    }

    public function test_returns_expired_state_when_expired() : void
    {
        $clock  = new FrozenClock(Timestamp::now());
        $policy = new CacheLifecyclePolicy();

        $lifecycle = CachedValueLifecycle::create(
            createdAt: $clock->now(),
            expiresAt: $clock->now()->subtract(Duration::ofSeconds(1)),
            clock    : $clock
        );

        $state = $policy->decide(lifecycle: $lifecycle, clock: $clock);

        $this->assertSame(CachedValueState::EXPIRED, $state);
    }

    public function test_returns_active_state_for_valid_entry() : void
    {
        $clock  = new FrozenClock(Timestamp::now());
        $policy = new CacheLifecyclePolicy();

        $lifecycle = CachedValueLifecycle::create(
            createdAt: $clock->now(),
            expiresAt: $clock->now()->add(Duration::ofSeconds(3600)),
            clock    : $clock
        );

        $state = $policy->decide(lifecycle: $lifecycle, clock: $clock);

        $this->assertSame(CachedValueState::ACTIVE, $state);
    }

    public function test_returns_expiring_soon_state() : void
    {
        $clock  = new FrozenClock(Timestamp::now());
        $policy = new CacheLifecyclePolicy(expiringSoonThresholdSeconds: 60);

        $lifecycle = CachedValueLifecycle::create(
            createdAt: $clock->now(),
            expiresAt: $clock->now()->add(Duration::ofSeconds(30)),
            clock    : $clock
        );

        $state = $policy->decide(lifecycle: $lifecycle, clock: $clock);

        $this->assertSame(CachedValueState::EXPIRING_SOON, $state);
    }

    public function test_returns_invalidated_state_when_explicitly_invalidated() : void
    {
        $clock  = new FrozenClock(Timestamp::now());
        $policy = new CacheLifecyclePolicy();

        $lifecycle = CachedValueLifecycle::create(
            createdAt: $clock->now(),
            expiresAt: $clock->now()->add(Duration::ofSeconds(3600)),
            clock    : $clock
        );

        $state = $policy->decide(
            lifecycle               : $lifecycle,
            clock                   : $clock,
            wasExplicitlyInvalidated: true
        );

        $this->assertSame(CachedValueState::INVALIDATED, $state);
    }

    public function test_returns_evicted_state_when_evicted() : void
    {
        $clock  = new FrozenClock(Timestamp::now());
        $policy = new CacheLifecyclePolicy();

        $state = $policy->decide(
            lifecycle : null,
            clock     : $clock,
            wasEvicted: true
        );

        $this->assertSame(CachedValueState::EVICTED, $state);
    }
}