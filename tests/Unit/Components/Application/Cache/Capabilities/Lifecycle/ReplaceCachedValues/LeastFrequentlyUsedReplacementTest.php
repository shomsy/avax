<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Capabilities\Lifecycle\ReplaceCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastFrequentlyUsedReplacement\LeastFrequentlyUsedReplacement;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use Override;
use PHPUnit\Framework\TestCase;

final class LeastFrequentlyUsedReplacementTest extends TestCase
{
    private FrozenClock $frozenClock;

    public function test_chooses_least_frequently_accessed() : void
    {
        $leastFrequentlyUsedReplacement = new LeastFrequentlyUsedReplacement(clock: $this->frozenClock);

        $entries = [
            'key_1' => $this->makeLifecycle(),
            'key_2' => $this->makeLifecycle(),
            'key_3' => $this->makeLifecycle(),
        ];

        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_1');
        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_1');
        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_2');

        $chosen = $leastFrequentlyUsedReplacement->choose(entries: $entries);

        $this->assertEquals('key_3', $chosen);
    }

    private function makeLifecycle(int $createdOffset = 0) : CachedValueLifecycle
    {
        $now       = $this->frozenClock->now();
        $timestamp = $now->add(duration: Duration::ofSeconds(seconds: $createdOffset));

        return CachedValueLifecycle::create(
            createdAt: $timestamp,
            expiresAt: $now->add(duration: Duration::ofSeconds(seconds: 3600)),
            clock    : $this->frozenClock,
        );
    }

    public function test_returns_null_on_empty_entries() : void
    {
        $leastFrequentlyUsedReplacement = new LeastFrequentlyUsedReplacement(clock: $this->frozenClock);

        $chosen = $leastFrequentlyUsedReplacement->choose(entries: []);

        $this->assertNull($chosen);
    }

    public function test_frequency_increments_on_each_access() : void
    {
        $leastFrequentlyUsedReplacement = new LeastFrequentlyUsedReplacement(clock: $this->frozenClock);

        $this->assertEquals(0, $leastFrequentlyUsedReplacement->getFrequency(key: 'key_1'));

        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_1');
        $this->assertEquals(1, $leastFrequentlyUsedReplacement->getFrequency(key: 'key_1'));

        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_1');
        $this->assertEquals(2, $leastFrequentlyUsedReplacement->getFrequency(key: 'key_1'));

        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_1');
        $this->assertEquals(3, $leastFrequentlyUsedReplacement->getFrequency(key: 'key_1'));
    }

    public function test_remove_clears_frequency() : void
    {
        $leastFrequentlyUsedReplacement = new LeastFrequentlyUsedReplacement(clock: $this->frozenClock);

        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_1');
        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_1');
        $this->assertEquals(2, $leastFrequentlyUsedReplacement->getFrequency(key: 'key_1'));

        $leastFrequentlyUsedReplacement->removeKey(key: 'key_1');
        $this->assertEquals(0, $leastFrequentlyUsedReplacement->getFrequency(key: 'key_1'));
    }

    public function test_reset_clears_all_frequencies() : void
    {
        $leastFrequentlyUsedReplacement = new LeastFrequentlyUsedReplacement(clock: $this->frozenClock);

        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_1');
        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_2');
        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_3');

        $this->assertEquals(1, $leastFrequentlyUsedReplacement->getFrequency(key: 'key_1'));
        $this->assertEquals(1, $leastFrequentlyUsedReplacement->getFrequency(key: 'key_2'));
        $this->assertEquals(1, $leastFrequentlyUsedReplacement->getFrequency(key: 'key_3'));

        $leastFrequentlyUsedReplacement->reset();

        $this->assertEquals(0, $leastFrequentlyUsedReplacement->getFrequency(key: 'key_1'));
        $this->assertEquals(0, $leastFrequentlyUsedReplacement->getFrequency(key: 'key_2'));
        $this->assertEquals(0, $leastFrequentlyUsedReplacement->getFrequency(key: 'key_3'));
    }

    public function test_all_same_frequency_returns_first() : void
    {
        $leastFrequentlyUsedReplacement = new LeastFrequentlyUsedReplacement(clock: $this->frozenClock);

        $entries = [
            'key_1' => $this->makeLifecycle(),
            'key_2' => $this->makeLifecycle(),
        ];

        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_1');
        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_2');

        $chosen = $leastFrequentlyUsedReplacement->choose(entries: $entries);

        $this->assertContains($chosen, ['key_1', 'key_2']);
    }

    public function test_unaccessed_key_is_chosen_first() : void
    {
        $leastFrequentlyUsedReplacement = new LeastFrequentlyUsedReplacement(clock: $this->frozenClock);

        $entries = [
            'key_1' => $this->makeLifecycle(),
            'key_2' => $this->makeLifecycle(),
            'key_3' => $this->makeLifecycle(),
        ];

        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_1');
        $leastFrequentlyUsedReplacement->recordAccess(key: 'key_2');

        $chosen = $leastFrequentlyUsedReplacement->choose(entries: $entries);

        $this->assertEquals('key_3', $chosen);
    }

    #[Override]
    protected function setUp() : void
    {
        $this->frozenClock = new FrozenClock(timestamp: Timestamp::now());
    }
}
