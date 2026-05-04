<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesign\LatencyBudget;

use Avax\Components\SystemDesign\LatencyBudget\System\Flows\AllocateLatency\AllocateLatency;
use Avax\Components\SystemDesign\LatencyBudget\System\PublicSurface\LatencyBudget;
use Avax\Tests\TestCase;

final class LatencyBudgetTest extends TestCase
{
    public function test_latency_budget_defines_slas(): void
    {
        $budget = new LatencyBudget(
            p50Ms: 50,
            p95Ms: 150,
            p99Ms: 300,
        );

        $this->assertSame(50, $budget->p50Ms);
        $this->assertSame(300, $budget->p99Ms);
    }

    public function test_allocate_latency_splits_budget(): void
    {
        $flow = new AllocateLatency();
        $result = $flow(new LatencyBudget(p50Ms: 50), 100);

        $this->assertSame(10, $result['http']);
        $this->assertSame(15, $result['cache']);
        $this->assertSame(40, $result['database']);
    }
}
