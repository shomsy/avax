<?php

declare(strict_types=1);

namespace Avax\Components\mDesign\Consistency;

use Avax\Components\Application\SystemDesign\Consistency\System\Flows\AnalyzeConsistency\AnalyzeConsistency;
use Avax\Components\Application\SystemDesign\Consistency\System\PublicSurface\ConsistencyLevel;
use Avax\Tests\TestCase;

final class ConsistencyLevelTest extends TestCase
{
    public function test_analyzes_eventual_consistency(): void
    {
        $flow = new AnalyzeConsistency();
        $result = $flow(ConsistencyLevel::EVENTUAL);

        $this->assertSame('eventual', $result['level']);
        $this->assertSame(5000, $result['latencyMs']);
        $this->assertSame('low', $result['complexity']);
    }

    public function test_analyzes_linearizable_consistency(): void
    {
        $flow = new AnalyzeConsistency();
        $result = $flow(ConsistencyLevel::LINEARIZABLE);

        $this->assertSame('linearizable', $result['level']);
        $this->assertSame(10, $result['latencyMs']);
        $this->assertSame('very-high', $result['complexity']);
    }

    public function test_all_consistency_levels_exist(): void
    {
        $this->assertCount(5, ConsistencyLevel::cases());
    }
}
