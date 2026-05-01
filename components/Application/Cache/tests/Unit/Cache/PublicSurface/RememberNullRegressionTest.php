<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\tests\Unit\Cache\PublicSurface;

use Avax\Components\Application\Cache\System\AvaxCache;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use Avax\Tests\TestCase;
use Override;
use Psr\SimpleCache\InvalidArgumentException;

final class RememberNullRegressionTest extends TestCase
{
    private FrozenClock $frozenClock;

    private InMemoryCacheStore $inMemoryCacheStore;

    private AvaxCache $avaxCache;

    /**
     * @throws InvalidArgumentException
     */
    public function test_it_does_not_reload_when_cached_value_is_null() : void
    {
        $this->avaxCache->set(key: 'nullable', value: null);

        $loadCount = 0;

        $result = $this->avaxCache->remember(key: 'nullable', ttl: 3600, loader: static function () use (&$loadCount) : string {
            $loadCount++;

            return 'loaded';
        });

        $this->assertNull(actual: $result);
        $this->assertSame(expected: 0, actual: $loadCount);
    }

    public function test_it_does_reload_when_key_does_not_exist() : void
    {
        $loadCount = 0;

        $result = $this->avaxCache->remember(key: 'missing', ttl: 3600, loader: static function () use (&$loadCount) : string {
            $loadCount++;

            return 'loaded';
        });

        $this->assertSame(expected: 'loaded', actual: $result);
        $this->assertSame(expected: 1, actual: $loadCount);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_it_reloads_when_value_is_not_null() : void
    {
        $this->avaxCache->set(key: 'exists', value: 'not_null');

        $loadCount = 0;

        $result = $this->avaxCache->remember(key: 'exists', ttl: 3600, loader: static function () use (&$loadCount) : string {
            $loadCount++;

            return 'loaded';
        });

        $this->assertSame(expected: 'not_null', actual: $result);
        $this->assertSame(expected: 0, actual: $loadCount);
    }

    #[Override]
    protected function setUp() : void
    {
        parent::setUp();
        $this->frozenClock = new FrozenClock(timestamp: Timestamp::now());
        $this->inMemoryCacheStore = new InMemoryCacheStore(
            clock: $this->frozenClock,
        );
        $this->avaxCache = new AvaxCache(
            store: $this->inMemoryCacheStore,
            clock: $this->frozenClock,
        );
    }
}
