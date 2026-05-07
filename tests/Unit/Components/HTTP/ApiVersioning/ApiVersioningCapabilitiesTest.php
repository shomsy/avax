<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\ApiVersioning;

use Avax\Components\HTTP\ApiVersioning\System\System\Capabilities\Lifecycle\VersionRegistry;
use PHPUnit\Framework\TestCase;

final class ApiVersioningCapabilitiesTest extends TestCase
{
    public function test_version_registry_registers_versions() : void
    {
        $registry = new VersionRegistry(currentVersion: 2, supportedVersions: [1]);
        $registry->support(3);

        $this->assertSame(2, $registry->current());
        $this->assertSame([1, 2, 3], $registry->supported());
    }
}
