<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\HealthCheck;

use Avax\Components\DataStack\Database\System\Capabilities\HealthCheck\CheckDatabaseHealth;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DatabaseHealthTest extends TestCase
{
    #[Test]
    public function checkReturnsHealthReport() : void
    {
        $healthCheck = new CheckDatabaseHealth();
        $report      = $healthCheck->check();

        self::assertInstanceOf(HealthReport::class, $report);
        self::assertNotEmpty($report->findings);
    }

    #[Test]
    public function checkProducesFindings() : void
    {
        $healthCheck = new CheckDatabaseHealth();
        $report      = $healthCheck->check();

        self::assertGreaterThanOrEqual(1, count($report->findings));

        foreach ($report->findings as $finding) {
            self::assertNotEmpty($finding->check);
            self::assertInstanceOf(HealthStatus::class, $finding->status);
        }
    }
}
