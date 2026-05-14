<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Security\Redaction\HealthCheck;

use Avax\Components\Security\Redaction\System\Capabilities\HealthCheck\CheckRedactionHealth;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RedactionHealthTest extends TestCase
{
    #[Test]
    public function checkReturnsHealthReport() : void
    {
        $healthCheck = new CheckRedactionHealth();
        $report      = $healthCheck->check();

        self::assertInstanceOf(HealthReport::class, $report);
        self::assertNotEmpty($report->findings);
    }

    #[Test]
    public function checkVerifiesRedactionEngine() : void
    {
        $healthCheck = new CheckRedactionHealth();
        $report      = $healthCheck->check();

        $checks = array_map(static fn ($f) => $f->check, $report->findings);

        self::assertContains('redaction.engine', $checks);
        self::assertContains('redaction.matcher', $checks);
    }

    #[Test]
    public function checkProducesValidStatus() : void
    {
        $healthCheck = new CheckRedactionHealth();
        $report      = $healthCheck->check();

        self::assertContains($report->overall, [HealthStatus::Green, HealthStatus::Yellow, HealthStatus::Red]);
    }
}
