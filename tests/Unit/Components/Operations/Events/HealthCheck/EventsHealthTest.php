<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Events\HealthCheck;

use Avax\Components\Operations\Events\System\Capabilities\HealthCheck\CheckEventsHealth;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EventsHealthTest extends TestCase
{
    #[Test]
    public function checkReturnsHealthReport() : void
    {
        $healthCheck = new CheckEventsHealth();
        $report      = $healthCheck->check();

        self::assertInstanceOf(HealthReport::class, $report);
        self::assertNotEmpty($report->findings);
    }

    #[Test]
    public function checkVerifiesRegistryAndEmitter() : void
    {
        $healthCheck = new CheckEventsHealth();
        $report      = $healthCheck->check();

        $checks = array_map(static fn ($f) => $f->check, $report->findings);

        self::assertContains('events.registry', $checks);
        self::assertContains('events.emitter', $checks);
    }

    #[Test]
    public function checkProducesValidStatus() : void
    {
        $healthCheck = new CheckEventsHealth();
        $report      = $healthCheck->check();

        self::assertContains($report->overall, [HealthStatus::Green, HealthStatus::Yellow, HealthStatus::Red]);
    }
}
