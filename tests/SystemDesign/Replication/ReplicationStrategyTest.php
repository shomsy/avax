<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesign\Replication;

use Avax\Components\SystemDesign\Replication\System\Flows\AnalyzeReplication\AnalyzeReplication;
use Avax\Components\SystemDesign\Replication\System\PublicSurface\ReplicationStrategy;
use Avax\Tests\TestCase;

final class ReplicationStrategyTest extends TestCase
{
    public function test_all_replication_strategies_exist(): void
    {
        $this->assertCount(3, ReplicationStrategy::cases());
    }

    public function test_analyze_replication_single_leader(): void
    {
        $flow = new AnalyzeReplication();
        $result = $flow(ReplicationStrategy::SINGLE_LEADER, 3);

        $this->assertSame('single-leader', $result['strategy']);
        $this->assertSame('strong', $result['consistency']);
    }
}
