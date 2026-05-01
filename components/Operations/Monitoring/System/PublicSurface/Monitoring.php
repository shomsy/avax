<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Monitoring\System\PublicSurface;

use Avax\Components\Operations\Monitoring\System\Capabilities\Dashboard\MonitoringDashboard;
use Avax\Components\Operations\Monitoring\System\Capabilities\ErrorReporting\SentryReporter;
use Avax\Components\Operations\Monitoring\System\Capabilities\Health\HealthEndpoint;
use Avax\Components\Operations\Monitoring\System\Capabilities\Health\HealthReport;
use Avax\Components\Operations\Monitoring\System\Capabilities\Metrics\MetricsRegistry;
use Throwable;

final class Monitoring
{
    private static ?MetricsRegistry $metrics = null;

    public static function dashboard(): array
    {
        return (new MonitoringDashboard(
            metrics: self::metrics(),
            health : self::health(),
        ))->data();
    }

    public static function metrics(): MetricsRegistry
    {
        if (self::$metrics === null) {
            self::$metrics = new MetricsRegistry;
        }

        return self::$metrics;
    }

    public static function health(array $checks = []): HealthReport
    {
        return (new HealthEndpoint)->report(checks: $checks);
    }

    public static function report(Throwable $throwable): void
    {
        (new SentryReporter)->capture(throwable: $throwable);
    }
}
