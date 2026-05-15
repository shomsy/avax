<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\ApiVersioning;

use Avax\Components\HTTP\ApiVersioning\System\Capabilities\Lifecycle\VersionRegistry;
use Avax\Components\HTTP\ApiVersioning\System\PublicSurface\ApiVersion;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ApiVersionLifecycleTest extends TestCase
{
    protected function tearDown(): void
    {
        ApiVersion::reset();
    }

    public function test_unconfigured_usage_fails_clearly() : void
    {
        ApiVersion::reset();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ApiVersion registry not configured');

        ApiVersion::current();
    }

    public function test_setInstance_wires_registry() : void
    {
        $custom = new VersionRegistry(currentVersion: 10, supportedVersions: [1, 2]);
        ApiVersion::setInstance($custom);

        $this->assertSame(10, ApiVersion::current());
        $this->assertSame([1, 2, 10], ApiVersion::supported());
    }

    public function test_double_boot_fails() : void
    {
        ApiVersion::setInstance(new VersionRegistry(currentVersion: 1, supportedVersions: [1]));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already configured');

        ApiVersion::setInstance(new VersionRegistry(currentVersion: 2, supportedVersions: [1, 2]));
    }

    public function test_reset_clears_static_state() : void
    {
        ApiVersion::setInstance(new VersionRegistry(currentVersion: 5, supportedVersions: [1, 2, 3, 4]));
        $this->assertSame(5, ApiVersion::current());

        ApiVersion::reset();

        // After reset, usage should fail clearly
        $this->expectException(RuntimeException::class);
        ApiVersion::current();
    }

    public function test_reset_provides_test_isolation() : void
    {
        // Simulate test A configuring state
        ApiVersion::setInstance(new VersionRegistry(currentVersion: 100, supportedVersions: []));
        $this->assertSame(100, ApiVersion::current());

        // Simulate test B starting after reset
        ApiVersion::reset();

        // Test B's first usage should fail until it configures its own registry
        $this->expectException(RuntimeException::class);
        ApiVersion::current();
    }
}
