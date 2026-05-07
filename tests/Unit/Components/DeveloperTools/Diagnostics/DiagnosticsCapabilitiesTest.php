<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DeveloperTools\Diagnostics;

use Avax\Components\DeveloperTools\Diagnostics\System\Capabilities\MemoryUsage;
use Avax\Components\DeveloperTools\Diagnostics\System\PublicSurface\CheckResult;
use Avax\Components\DeveloperTools\Diagnostics\System\PublicSurface\HealthReport;
use PHPUnit\Framework\TestCase;

final class DiagnosticsCapabilitiesTest extends TestCase
{
    public function test_memory_usage_reporting() : void
    {
        $usage = MemoryUsage::execute();

        $this->assertGreaterThan(0, $usage);
    }

    public function test_health_report_structure() : void
    {
        $report = new HealthReport(
            status: 'green',
            checks: [
                        'memory' => new CheckResult(status: 'ok')
                    ]
        );

        $this->assertSame('green', $report->status);
        $this->assertCount(1, $report->checks);
    }
}
