<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4Health;

use Avax\Framework\System\Capabilities\Doctor\Types\DoctorFinding;
use Avax\Framework\System\Capabilities\Doctor\Types\DoctorSeverity;
use Avax\Framework\System\Capabilities\Health\CheckLiveness;
use Avax\Framework\System\Capabilities\Health\CheckReadiness;
use Avax\Framework\System\Capabilities\Health\Foundation\DiagnosticFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\DiagnosticSeverity;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use Avax\Framework\System\Capabilities\Health\Foundation\ProductionReadinessVerdict;
use Avax\Framework\System\Capabilities\Health\Foundation\ReadinessReport;
use Avax\Framework\System\Capabilities\Health\Foundation\RuntimeStatusSnapshot;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class V4HealthTest extends TestCase
{
    #[Test]
    public function health_returns_ok(): void
    {
        $report = HealthReport::healthy();

        self::assertSame(HealthStatus::Green, $report->overall);
        self::assertCount(1, $report->findings);
    }

    #[Test]
    public function liveness_returns_alive(): void
    {
        $check = new CheckLiveness([static fn () => true]);

        $report = $check->check();

        self::assertSame(HealthStatus::Green, $report->overall);
    }

    #[Test]
    public function liveness_fails_if_check_returns_false(): void
    {
        $check = new CheckLiveness([static fn () => false]);

        $report = $check->check();

        self::assertSame(HealthStatus::Red, $report->overall);
    }

    #[Test]
    public function readiness_passes_when_all_checks_green(): void
    {
        $check = new CheckReadiness([
            static fn () => new HealthFinding('db', HealthStatus::Green, 'Connected.'),
            static fn () => new HealthFinding('cache', HealthStatus::Green, 'Available.'),
        ]);

        $report = $check->check();

        self::assertSame(HealthStatus::Green, $report->status);
        self::assertCount(2, $report->checks);
    }

    #[Test]
    public function readiness_fails_if_db_unhealthy(): void
    {
        $check = new CheckReadiness([
            static fn () => new HealthFinding('db', HealthStatus::Red, 'Connection refused.'),
            static fn () => new HealthFinding('cache', HealthStatus::Green, 'Available.'),
        ]);

        $report = $check->check();

        self::assertSame(HealthStatus::Red, $report->status);
    }

    #[Test]
    public function readiness_yellow_if_partial_degradation(): void
    {
        $check = new CheckReadiness([
            static fn () => new HealthFinding('db', HealthStatus::Green, 'Connected.'),
            static fn () => new HealthFinding('cache', HealthStatus::Yellow, 'Slow response.'),
        ]);

        $report = $check->check();

        self::assertSame(HealthStatus::Yellow, $report->status);
    }

    #[Test]
    public function production_verdict_combines_findings(): void
    {
        $findings = [
            new HealthFinding('autoload', HealthStatus::Green, 'OK'),
            new HealthFinding('config', HealthStatus::Green, 'OK'),
            new HealthFinding('security', HealthStatus::Red, 'No signing secret'),
        ];

        $verdict = ProductionReadinessVerdict::fromFindings($findings, '1.0.0');

        self::assertSame(HealthStatus::Red, $verdict->status);
        self::assertSame(2, $verdict->passedCount);
        self::assertSame(1, $verdict->failedCount);
    }

    #[Test]
    public function health_report_to_array(): void
    {
        $report = new HealthReport(
            findings: [new HealthFinding('test', HealthStatus::Green, 'OK')],
            overall: HealthStatus::Green,
        );

        $array = $report->toArray();

        self::assertSame('green', $array['status']);
        self::assertCount(1, $array['findings']);
    }

    #[Test]
    public function readiness_report_to_array(): void
    {
        $report = new ReadinessReport(
            status: HealthStatus::Green,
            checks: [new HealthFinding('db', HealthStatus::Green, 'OK')],
            version: '1.0.0',
        );

        $array = $report->toArray();

        self::assertSame('green', $array['status']);
        self::assertSame('1.0.0', $array['version']);
    }

    #[Test]
    public function diagnostic_finding(): void
    {
        $finding = new DiagnosticFinding('memory', DiagnosticSeverity::Warning, 'High memory usage.');

        self::assertSame('memory', $finding->component);
        self::assertSame(DiagnosticSeverity::Warning, $finding->severity);
    }

    #[Test]
    public function runtime_status_snapshot(): void
    {
        $snapshot = new RuntimeStatusSnapshot(
            status: HealthStatus::Green,
            uptime: 3600,
            memoryBytes: 64 * 1024 * 1024,
            findings: [new HealthFinding('memory', HealthStatus::Green, 'OK')],
        );

        self::assertSame(3600, $snapshot->uptime);
        self::assertSame(64 * 1024 * 1024, $snapshot->memoryBytes);
    }

    #[Test]
    public function health_finding_immutable(): void
    {
        $finding = new HealthFinding('test', HealthStatus::Red, 'Failure');

        self::assertSame('test', $finding->check);
        self::assertSame(HealthStatus::Red, $finding->status);
        self::assertSame('Failure', $finding->message);
    }

    #[Test]
    public function production_verdict_all_green(): void
    {
        $findings = [
            new HealthFinding('a', HealthStatus::Green),
            new HealthFinding('b', HealthStatus::Green),
        ];

        $verdict = ProductionReadinessVerdict::fromFindings($findings);

        self::assertSame(HealthStatus::Green, $verdict->status);
        self::assertSame(2, $verdict->passedCount);
        self::assertSame(0, $verdict->failedCount);
    }
}
