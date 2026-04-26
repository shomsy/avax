<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Capabilities\Lifecycle\ReplaceCachedValues;

use Avax\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastFrequentlyUsedReplacement\LeastFrequentlyUsedReplacement;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\FrozenClock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

final class LeastFrequentlyUsedReplacementTest extends TestCase
{
    private FrozenClock $clock;

    public function test_chooses_least_frequently_accessed() : void
    {
        $policy = new LeastFrequentlyUsedReplacement(clock: $this->clock);

        $entries = [
            'key_1' => $this->makeLifecycle(),
            'key_2' => $this->makeLifecycle(),
            'key_3' => $this->makeLifecycle(),
        ];

        $policy->recordAccess(key: 'key_1');
        $policy->recordAccess(key: 'key_1');
        $policy->recordAccess(key: 'key_2');

        $chosen = $policy->choose(entries: $entries);

        $this->assertEquals(expected: 'key_3', actual: $chosen);
    }

    private function makeLifecycle(int $createdOffset = 0) : CachedValueLifecycle
    {
        $now     = $this->clock->now();
        $created = $now->add(duration: Duration::ofSeconds(seconds: $createdOffset));

        return CachedValueLifecycle::create(
            createdAt: $created,
            expiresAt: $now->add(duration: Duration::ofSeconds(seconds: 3600)),
            clock    : $this->clock
        );
    }

    public function test_returns_null_on_empty_entries() : void
    {
        $policy = new LeastFrequentlyUsedReplacement(clock: $this->clock);

        $chosen = $policy->choose(entries: []);

        $this->assertNull(actual: $chosen);
    }

    public function test_frequency_increments_on_each_access() : void
    {
        $policy = new LeastFrequentlyUsedReplacement(clock: $this->clock);

        $this->assertEquals(expected: 0, actual: $policy->getFrequency(key: 'key_1'));

        $policy->recordAccess(key: 'key_1');
        $this->assertEquals(expected: 1, actual: $policy->getFrequency(key: 'key_1'));

        $policy->recordAccess(key: 'key_1');
        $this->assertEquals(expected: 2, actual: $policy->getFrequency(key: 'key_1'));

        $policy->recordAccess(key: 'key_1');
        $this->assertEquals(expected: 3, actual: $policy->getFrequency(key: 'key_1'));
    }

    public function test_remove_clears_frequency() : void
    {
        $policy = new LeastFrequentlyUsedReplacement(clock: $this->clock);

        $policy->recordAccess(key: 'key_1');
        $policy->recordAccess(key: 'key_1');
        $this->assertEquals(expected: 2, actual: $policy->getFrequency(key: 'key_1'));

        $policy->removeKey(key: 'key_1');
        $this->assertEquals(expected: 0, actual: $policy->getFrequency(key: 'key_1'));
    }

    public function test_reset_clears_all_frequencies() : void
    {
        $policy = new LeastFrequentlyUsedReplacement(clock: $this->clock);

        $policy->recordAccess(key: 'key_1');
        $policy->recordAccess(key: 'key_2');
        $policy->recordAccess(key: 'key_3');

        $this->assertEquals(expected: 1, actual: $policy->getFrequency(key: 'key_1'));
        $this->assertEquals(expected: 1, actual: $policy->getFrequency(key: 'key_2'));
        $this->assertEquals(expected: 1, actual: $policy->getFrequency(key: 'key_3'));

        $policy->reset();

        $this->assertEquals(expected: 0, actual: $policy->getFrequency(key: 'key_1'));
        $this->assertEquals(expected: 0, actual: $policy->getFrequency(key: 'key_2'));
        $this->assertEquals(expected: 0, actual: $policy->getFrequency(key: 'key_3'));
    }

    public function test_all_same_frequency_returns_first() : void
    {
        $policy = new LeastFrequentlyUsedReplacement(clock: $this->clock);

        $entries = [
            'key_1' => $this->makeLifecycle(),
            'key_2' => $this->makeLifecycle(),
        ];

        $policy->recordAccess(key: 'key_1');
        $policy->recordAccess(key: 'key_2');

        $chosen = $policy->choose(entries: $entries);

        $this->assertContains(needle: $chosen, haystack: ['key_1', 'key_2']);
    }

    public function test_unaccessed_key_is_chosen_first() : void
    {
        $policy = new LeastFrequentlyUsedReplacement(clock: $this->clock);

        $entries = [
            'key_1' => $this->makeLifecycle(),
            'key_2' => $this->makeLifecycle(),
            'key_3' => $this->makeLifecycle(),
        ];

        $policy->recordAccess(key: 'key_1');
        $policy->recordAccess(key: 'key_2');

        $chosen = $policy->choose(entries: $entries);

        $this->assertEquals(expected: 'key_3', actual: $chosen);
    }

    protected function setUp() : void
    {
        $this->clock = new FrozenClock(timestamp: Timestamp::now());
    }
}