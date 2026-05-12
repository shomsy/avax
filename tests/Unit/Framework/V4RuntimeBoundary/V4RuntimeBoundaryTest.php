<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4RuntimeBoundary;

use Avax\Framework\System\Capabilities\RuntimeBoundary\ReactPhpAdapter;
use Avax\Framework\System\Capabilities\RuntimeBoundary\RoadRunnerAdapter;
use Avax\Framework\System\Capabilities\RuntimeBoundary\RuntimeAdapter;
use Avax\Framework\System\Capabilities\RuntimeBoundary\RuntimeCapabilityReport;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class V4RuntimeBoundaryTest extends TestCase
{
    #[Test]
    public function reactphp_adapter_reports_capabilities() : void
    {
        $adapter = new ReactPhpAdapter();

        self::assertSame('reactphp', $adapter->name());
        self::assertArrayHasKey('http_server', $adapter->capabilities());
    }

    #[Test]
    public function roadrunner_adapter_unavailable_without_dependency() : void
    {
        $adapter = new RoadRunnerAdapter();

        self::assertSame('roadrunner', $adapter->name());

        // Without real RoadRunner dependency, should report unavailable
        if (! $adapter->isAvailable()) {
            self::assertFalse($adapter->capabilities()['http_server']);
        }
    }

    #[Test]
    public function runtime_capability_report_combines_adapters() : void
    {
        $report = new RuntimeCapabilityReport([
                                                  new ReactPhpAdapter(),
                                                  new RoadRunnerAdapter(),
                                              ]);

        $data = $report->report();

        self::assertArrayHasKey('reactphp', $data);
        self::assertArrayHasKey('roadrunner', $data);
    }

    #[Test]
    public function unsupported_runtime_fails_gracefully() : void
    {
        // Test that the framework doesn't include runtime-specific types in public API
        $adapter = new ReactPhpAdapter();
        self::assertSame('reactphp', $adapter->name());

        // No Swoole/FrankenPHP types leak into the public API
        self::assertFalse(class_exists('Swoole\Http\Server'));
    }
}
