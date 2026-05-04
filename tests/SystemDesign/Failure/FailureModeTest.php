<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesignKit\Failure;

use Avax\Components\SystemDesign\Failure\System\Flows\AnalyzeFailureMode\AnalyzeFailureMode;
use Avax\Components\SystemDesign\Failure\System\PublicSurface\FailureMode;
use Avax\Tests\TestCase;

final class FailureModeTest extends TestCase
{
    public function test_analyzes_timeout_failure(): void
    {
        $flow = new AnalyzeFailureMode();
        $result = $flow(FailureMode::TIMEOUT);

        $this->assertSame('timeout', $result['mode']);
        $this->assertTrue($result['recoverable']);
        $this->assertTrue($result['idempotent']);
        $this->assertSame('delay', $result['impact']);
    }

    public function test_analyzes_partition_failure(): void
    {
        $flow = new AnalyzeFailureMode();
        $result = $flow(FailureMode::PARTITION);

        $this->assertSame('partition', $result['mode']);
        $this->assertFalse($result['recoverable']);
        $this->assertSame('inconsistency', $result['impact']);
    }

    public function test_all_failure_modes_exist(): void
    {
        $this->assertCount(7, FailureMode::cases());
    }
}
