<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\V4DeveloperExperience;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Framework\System\Capabilities\Doctor\CheckAutoload;
use Avax\Framework\System\Capabilities\Doctor\CheckMemoryGuard;
use Avax\Framework\System\Capabilities\Doctor\CheckRuntimeMode;
use Avax\Framework\System\Capabilities\Doctor\CheckWarmSafety;
use Avax\Framework\System\Capabilities\Doctor\ExecuteDoctorChecks;
use Avax\Framework\System\Capabilities\Doctor\Types\DoctorFinding;
use Avax\Framework\System\Capabilities\Doctor\Types\DoctorReport;
use Avax\Framework\System\Capabilities\Doctor\Types\DoctorSeverity;
use Avax\Framework\System\Capabilities\Routing\CacheRouteTable;
use Avax\Framework\System\Capabilities\Routing\LoadCachedRoutes;
use Avax\Framework\System\Configuration\Foundation\ApplicationConfiguration;
use Avax\Framework\System\Configuration\Foundation\RuntimeConfiguration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DeveloperExperienceTest extends TestCase
{
    #[Test]
    public function doctorReportAggregatesFindings(): void
    {
        $report = new DoctorReport();
        $report->add(new DoctorFinding(check: 'autoload', severity: DoctorSeverity::Green, message: 'OK'));
        $report->add(new DoctorFinding(check: 'config', severity: DoctorSeverity::Green, message: 'OK'));

        self::assertCount(2, $report->findings());
        self::assertSame(DoctorSeverity::Green, $report->overallStatus());
    }

    #[Test]
    public function doctorReportRedWinsOverGreen(): void
    {
        $report = new DoctorReport();
        $report->add(new DoctorFinding(check: 'autoload', severity: DoctorSeverity::Green, message: 'OK'));
        $report->add(new DoctorFinding(check: 'config', severity: DoctorSeverity::Red, message: 'Broken'));

        self::assertSame(DoctorSeverity::Red, $report->overallStatus());
    }

    #[Test]
    public function doctorReportYellowWinsOverGreen(): void
    {
        $report = new DoctorReport();
        $report->add(new DoctorFinding(check: 'autoload', severity: DoctorSeverity::Green, message: 'OK'));
        $report->add(new DoctorFinding(check: 'config', severity: DoctorSeverity::Yellow, message: 'Warning'));

        self::assertSame(DoctorSeverity::Yellow, $report->overallStatus());
    }

    #[Test]
    public function doctorReportRenderProducesOutput(): void
    {
        $report = new DoctorReport();
        $report->add(new DoctorFinding(check: 'test', severity: DoctorSeverity::Green, message: 'OK'));

        $output = $report->render();

        self::assertStringContainsString('test', $output);
        self::assertStringContainsString('OK', $output);
        self::assertStringContainsString('GREEN', $output);
    }

    #[Test]
    public function executeDoctorChecksExecutesAllChecks() : void
    {
        $callCount = 0;
        $check = static function () use (&$callCount): DoctorFinding {
            $callCount++;

            return new DoctorFinding(check: 'counter', severity: DoctorSeverity::Green, message: "Called {$callCount}");
        };

        $executeChecks = new ExecuteDoctorChecks(checks: [$check, $check, $check]);
        $report        = $executeChecks->run();

        self::assertCount(3, $report->findings());
        self::assertSame(3, $callCount);
    }

    #[Test]
    public function checkAutoloadReturnsGreenWhenVendorExists(): void
    {
        $check = new CheckAutoload();
        $finding = $check();

        self::assertSame('autoload', $finding->check);
        // Should be green or yellow depending on optimized autoload
        self::assertContains($finding->severity, [DoctorSeverity::Green, DoctorSeverity::Yellow]);
    }

    #[Test]
    public function checkWarmSafetyReturnsGreenWhenFilesExist(): void
    {
        $check = new CheckWarmSafety();
        $finding = $check();

        self::assertSame('warm-safety', $finding->check);
        self::assertSame(DoctorSeverity::Green, $finding->severity);
    }

    #[Test]
    public function checkMemoryGuardReturnsGreenWhenFilesExist(): void
    {
        $check = new CheckMemoryGuard();
        $finding = $check();

        self::assertSame('memory-guard', $finding->check);
        self::assertSame(DoctorSeverity::Green, $finding->severity);
    }

    #[Test]
    public function checkRuntimeModeReturnsFinding(): void
    {
        $check = new CheckRuntimeMode();
        $finding = $check();

        self::assertSame('runtime-mode', $finding->check);
    }

    #[Test]
    public function applicationConfigurationDefaultsAreValid(): void
    {
        $config = new ApplicationConfiguration();

        self::assertNotEmpty($config->name);
        self::assertNotEmpty($config->environment);
        self::assertNotEmpty($config->timezone);
    }

    #[Test]
    public function runtimeConfigurationDefaultsAreValid(): void
    {
        $config = new RuntimeConfiguration();

        self::assertNotEmpty($config->host);
        self::assertGreaterThan(0, $config->port);
        self::assertGreaterThan(0, $config->memoryGuardSoftLimit);
        self::assertGreaterThan($config->memoryGuardSoftLimit, $config->memoryGuardHardLimit);
    }

    #[Test]
    public function routeCacheWritesAndLoads(): void
    {
        $tempDir = sys_get_temp_dir().'/avax-route-cache-test-'.time();
        $routeTable = ['GET /' => ['handler' => 'root']];
        $filesystem = new Filesystem();

        $cacheRouteTable = new CacheRouteTable(filesystem: $filesystem, cacheDirectory: $tempDir);
        $cacheFile = $cacheRouteTable->write(routeTable: $routeTable);

        self::assertFileExists($cacheFile);

        $loadCachedRoutes = new LoadCachedRoutes(filesystem: $filesystem, cacheDirectory: $tempDir);
        $loaded = $loadCachedRoutes->load();

        self::assertSame($routeTable, $loaded);

        // Cleanup
        $loadCachedRoutes->clear();
        rmdir($tempDir);
    }

    #[Test]
    public function routeCacheClearWorks(): void
    {
        $tempDir = sys_get_temp_dir().'/avax-route-cache-clear-test-'.time();
        $routeTable = ['GET /test' => ['handler' => 'test']];
        $filesystem = new Filesystem();

        $cacheRouteTable = new CacheRouteTable(filesystem: $filesystem, cacheDirectory: $tempDir);
        $cacheRouteTable->write(routeTable: $routeTable);

        $loadCachedRoutes = new LoadCachedRoutes(filesystem: $filesystem, cacheDirectory: $tempDir);

        self::assertTrue($loadCachedRoutes->exists());

        $result = $loadCachedRoutes->clear();

        self::assertTrue($result);
        self::assertFalse($loadCachedRoutes->exists());

        // Cleanup
        rmdir($tempDir);
    }
}
