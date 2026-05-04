<?php

declare(strict_types=1);

namespace Avax\Components\mDesign\Caching;

use Avax\Components\SystemDesign\Caching\System\Flows\AnalyzeCacheStrategy\AnalyzeCacheStrategy;
use Avax\Components\SystemDesign\Caching\System\PublicSurface\CacheStrategy;
use Avax\Tests\TestCase;

final class CacheStrategyTest extends TestCase
{
    public function test_analyzes_cache_aside_strategy(): void
    {
        $flow = new AnalyzeCacheStrategy();
        $result = $flow(CacheStrategy::CACHE_ASIDE);

        $this->assertSame('cache-aside', $result['strategy']);
        $this->assertSame('eventual', $result['consistency']);
        $this->assertSame(1, $result['writeAmplification']);
        $this->assertSame('high', $result['stampedeRisk']);
    }

    public function test_analyzes_write_through_strategy(): void
    {
        $flow = new AnalyzeCacheStrategy();
        $result = $flow(CacheStrategy::WRITE_THROUGH);

        $this->assertSame('write-through', $result['strategy']);
        $this->assertSame('strong', $result['consistency']);
        $this->assertSame(2, $result['writeAmplification']);
        $this->assertSame('low', $result['stampedeRisk']);
    }

    public function test_all_cache_strategies_exist(): void
    {
        $this->assertCount(4, CacheStrategy::cases());
    }
}
