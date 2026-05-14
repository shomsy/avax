<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\FailureBoundary\HealthCheck;

use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\HealthCheck\CheckFailureBoundaryHealth;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FailureBoundaryHealthTest extends TestCase
{
    #[Test]
    public function checkReturnsHealthReport() : void
    {
        $healthCheck = new CheckFailureBoundaryHealth();
        $report      = $healthCheck->check();

        self::assertInstanceOf(HealthReport::class, $report);
        self::assertNotEmpty($report->findings);
    }

    #[Test]
    public function checkVerifiesPolicyAndCache() : void
    {
        $healthCheck = new CheckFailureBoundaryHealth();
        $report      = $healthCheck->check();

        $checks = array_map(static fn ($f) => $f->check, $report->findings);

        self::assertContains('failure.policy', $checks);
        self::assertContains('failure.cache', $checks);
    }

    #[Test]
    public function checkProducesValidStatus() : void
    {
        $healthCheck = new CheckFailureBoundaryHealth();
        $report      = $healthCheck->check();

        self::assertContains($report->overall, [HealthStatus::Green, HealthStatus::Yellow, HealthStatus::Red]);
    }
}
