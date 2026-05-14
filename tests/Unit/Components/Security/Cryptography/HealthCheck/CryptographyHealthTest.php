<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Security\Cryptography\HealthCheck;

use Avax\Components\Security\Cryptography\System\Capabilities\HealthCheck\CheckCryptographyHealth;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CryptographyHealthTest extends TestCase
{
    #[Test]
    public function checkReturnsHealthReport() : void
    {
        $healthCheck = new CheckCryptographyHealth();
        $report      = $healthCheck->check();

        self::assertInstanceOf(HealthReport::class, $report);
        self::assertNotEmpty($report->findings);
    }

    #[Test]
    public function checkVerifiesOpenSslExtension() : void
    {
        $healthCheck = new CheckCryptographyHealth();
        $report      = $healthCheck->check();

        $checks = array_map(static fn ($f) => $f->check, $report->findings);

        self::assertContains('cryptography.openssl', $checks);
    }

    #[Test]
    public function checkProducesValidStatus() : void
    {
        $healthCheck = new CheckCryptographyHealth();
        $report      = $healthCheck->check();

        self::assertContains($report->overall, [HealthStatus::Green, HealthStatus::Yellow, HealthStatus::Red]);
    }
}
