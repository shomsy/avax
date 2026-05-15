<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\PublicSurface;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastRecentlyUsedReplacement;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Configuration\Builders\BuildCache;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use Avax\Components\Application\Cache\System\PublicSurface\AvaxCache;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Tests\TestCase;
use Override;
use Psr\SimpleCache\InvalidArgumentException;

final class RememberNullRegressionTest extends TestCase
{
    private FrozenClock $frozenClock;

    private InMemoryCacheStore $inMemoryCacheStore;

    private AvaxCache $avaxCache;

    public function test_it_does_not_reload_when_cached_value_is_null() : void
    {
        $this->avaxCache->set(value: null, key: 'nullable');

        $loadCount = 0;

        $result = $this->avaxCache->remember(ttl: 3600, loader: static function () use (&$loadCount) : string {
            $loadCount++;

            return 'loaded';
        },                                   key: 'nullable');

        $this->assertNull($result);
        $this->assertSame(0, $loadCount);
    }

    public function test_it_does_reload_when_key_does_not_exist() : void
    {
        $loadCount = 0;

        $result = $this->avaxCache->remember(ttl: 3600, loader: static function () use (&$loadCount) : string {
            $loadCount++;

            return 'loaded';
        },                                   key: 'missing');

        $this->assertSame('loaded', $result);
        $this->assertSame(1, $loadCount);
    }

    public function test_it_reloads_when_value_is_not_null() : void
    {
        $this->avaxCache->set(value: 'not_null', key: 'exists');

        $loadCount = 0;

        $result = $this->avaxCache->remember(ttl: 3600, loader: static function () use (&$loadCount) : string {
            $loadCount++;

            return 'loaded';
        },                                   key: 'exists');

        $this->assertSame('not_null', $result);
        $this->assertSame(0, $loadCount);
    }

    #[Override]
    protected function setUp() : void
    {
        parent::setUp();
        $this->frozenClock        = new FrozenClock(timestamp: Timestamp::now());
        $this->inMemoryCacheStore = new InMemoryCacheStore(
            clock: $this->frozenClock,
            chooseCachedValueForReplacement: new LeastRecentlyUsedReplacement(),
        );
        $buildCache = new BuildCache(clock: $this->frozenClock, filesystem: new Filesystem());
        $this->avaxCache = $buildCache->fromStore(store: $this->inMemoryCacheStore);
    }
}
