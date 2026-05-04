<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesign\Partitioning;

use Avax\Components\Application\SystemDesign\Partitioning\System\Flows\DesignPartitioning\DesignPartitioning;
use Avax\Components\Application\SystemDesign\Partitioning\System\PublicSurface\PartitioningStrategy;
use Avax\Tests\TestCase;

final class PartitioningStrategyTest extends TestCase
{
    public function test_all_partitioning_strategies_exist(): void
    {
        $this->assertCount(4, PartitioningStrategy::cases());
    }

    public function test_design_partitioning_analyzes_hash_strategy(): void
    {
        $flow = new DesignPartitioning();
        $result = $flow(PartitioningStrategy::HASH, 16);

        $this->assertSame('hash', $result['strategy']);
        $this->assertSame('low', $result['hotSpotRisk']);
    }
}
