<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\ApiVersioning;

use Avax\Components\HTTP\ApiVersioning\System\Capabilities\Lifecycle\VersionRegistry;
use Avax\Components\HTTP\ApiVersioning\System\PublicSurface\ApiVersion;
use PHPUnit\Framework\TestCase;

final class ApiVersionLifecycleTest extends TestCase
{
    protected function tearDown(): void
    {
        ApiVersion::reset();
    }

    public function test_reset_clears_static_registry() : void
    {
        // Configure a custom registry
        $custom = new VersionRegistry(currentVersion: 5, supportedVersions: [1, 2, 3, 4]);
        ApiVersion::setInstance($custom);

        $this->assertSame(5, ApiVersion::current());

        // Reset should clear the static state
        ApiVersion::reset();

        // After reset, registry returns to default (VersionRegistry with no current version)
        ApiVersion::supported();
    }

    public function test_setInstance_replaces_registry() : void
    {
        $custom = new VersionRegistry(currentVersion: 10, supportedVersions: [1, 2]);
        ApiVersion::setInstance($custom);

        $this->assertSame(10, ApiVersion::current());
        $this->assertSame([1, 2, 10], ApiVersion::supported());
    }

    public function test_reset_provides_test_isolation() : void
    {
        // Simulate test A setting state
        ApiVersion::setInstance(new VersionRegistry(currentVersion: 100, supportedVersions: []));
        $this->assertSame(100, ApiVersion::current());

        // Simulate test B starting after reset
        ApiVersion::reset();
        $this->assertNotSame(100, ApiVersion::current());
    }
}
