<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Logging\HealthCheck;

use Avax\Components\Operations\Logging\System\Capabilities\HealthCheck\CheckLoggingHealth;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LoggingHealthTest extends TestCase
{
    #[Test]
    public function checkReturnsHealthReport() : void
    {
        $healthCheck = new CheckLoggingHealth();
        $report      = $healthCheck->check();

        self::assertInstanceOf(HealthReport::class, $report);
        self::assertNotEmpty($report->findings);
    }

    #[Test]
    public function checkVerifiesLoggerWritable() : void
    {
        $healthCheck = new CheckLoggingHealth();
        $report      = $healthCheck->check();

        $checks = array_map(static fn ($f) => $f->check, $report->findings);

        self::assertContains('logging.writable', $checks);
    }

    #[Test]
    public function checkProducesValidStatus() : void
    {
        $healthCheck = new CheckLoggingHealth();
        $report      = $healthCheck->check();

        self::assertContains($report->overall, [HealthStatus::Green, HealthStatus::Yellow, HealthStatus::Red]);
    }
}
